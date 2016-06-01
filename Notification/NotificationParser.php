<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\BeforeParseNotificationEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Exception\NotificationException;

/**
 * Responsible for parsing notification request and performing entity edits
 *
 * Class NotificationParser
 */
class NotificationParser
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var object EntityManagerInterface */
    protected $entityManager;

    /** @var  EntityConversionHandler */
    protected $conversionHandler;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  EntityAssociator */
    protected $entityAssociator;

    /** @var array Array of request data */
    protected $notificationData;

    /** @var string Field name which will be mapped to the id from the notification request */
    protected $entityIdFieldName;

    /** @var  bool */
    protected $isClient;

    /**
     * @var array Indexed array of entities' aliases and real class names.
     * format:
     * [
     *    "user" => "App\Entity\User,
     *    "product" => "App\Entity\Product,
     *    ....
     * ]
     */
    protected $entities;


    /**
     * NotificationParser constructor.
     *
     * @param LoggerInterface          $logger
     * @param EntityConversionHandler  $conversionHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface   $entityManager
     * @param EntityAssociator         $entityAssociator
     * @param string                   $entityIdFieldName
     * @param bool                     $isClient
     * @param array                    $entities
     */
    public function __construct(
        LoggerInterface $logger,
        EntityConversionHandler $conversionHandler,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        EntityAssociator $entityAssociator,
        string $entityIdFieldName,
        bool $isClient,
        array $entities
    ) {
        $this->logger = $logger;
        $this->conversionHandler = $conversionHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->entityAssociator = $entityAssociator;
        $this->notificationData = [];
        $this->entityIdFieldName = $entityIdFieldName;
        $this->isClient = $isClient;
        $this->entities = $entities;
    }

    /**
     * @param array $notifications
     *
     * @return array
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws NotificationException
     */
    public function parseNotifications(array $notifications = []) : array
    {
        $processedEntities = [];

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $entityName = $notification->getData()['entityName'];

            if (!array_key_exists($entityName, $this->entities)) {
                throw new NotificationException(
                    "No classname found for entityName: '$entityName'." .
                    'Have you defined it in the configuration under trinity_notification:entities?'
                );
            }

            $processedEntities[] = $this->parseNotification(
                $notification->getData(),
                $this->entities[$entityName],
                $notification->getMethod()
            );

        }

        $this->entityAssociator->associate($processedEntities);

        return $processedEntities;
    }

    /**
     * @param  $data               array  Notification data as named array
     * @param  $fullClassName      string Full classname(with namespace) of the entity.e.g.
     *                             AppBundle\\Entity\\Product\\StandardProduct
     * @param  $HTTPMethod         string HTTP method of the request
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws NotificationException
     *
     * @return null|object Returns changed entity(on new[POST] or update[PUT]) or null on delete[DELETE]
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function parseNotification(array $data, string $fullClassName, string $HTTPMethod)
    {
        // If there are listeners for this event,
        // fire it and get the message from it(it allows changing the data, className and method)
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_PARSE_NOTIFICATION)) {
            $event = new BeforeParseNotificationEvent($data, $fullClassName, $HTTPMethod);
            /** @var BeforeParseNotificationEvent $event */
            $event = $this->eventDispatcher->dispatch(
                Events::BEFORE_PARSE_NOTIFICATION,
                $event
            );
            $data = $event->getData();
            $fullClassName = $event->getClassname();
            $HTTPMethod = $event->getHttpMethod();
        }

        $HTTPMethod = strtoupper($HTTPMethod);
        $this->notificationData = $data;

        //get existing entity from database or create a new one
        $entityObject = $this->getEntityObject($fullClassName);

        /*
        exist  && delete   - delete entity, without form
        !exist && delete   - throw exception -> error message
        exist  && post     - throw exception -> error message
        !exist && post     - use form to create an entity from notification
        !exist && put      - use form to create an entity from notification
        exist  && put      - use form to edit the entity with notification data
        */
        $this->checkLogicalViolations($entityObject, $fullClassName, $HTTPMethod);

        //delete entity, without form
        if ($entityObject !== null && $HTTPMethod === 'DELETE') {
            $this->logger->info('METHOD: DELETE ' . $HTTPMethod);
            $this->entityManager->remove($entityObject);

            return null;
        } elseif ($entityObject === null && $HTTPMethod === 'POST') {
            return $this->conversionHandler->performEntityCreate(
                array_search($fullClassName, $this->entities, true),
                $data
            );
        } elseif ($entityObject === null && $HTTPMethod === 'PUT') {
            return $this->conversionHandler->performEntityCreate(
                array_search($fullClassName, $this->entities, true),
                $data
            );
        } elseif ($entityObject !== null && $HTTPMethod === 'PUT') {
            return $this->conversionHandler->performEntityUpdate($entityObject, $data);
        } else {
            throw new NotificationException(
                "Unsupported combination of input conditions. Tried to apply method $HTTPMethod on " .
                ($entityObject ? 'existing' : 'non existing') . ' entity.'
            );
        }
    }

    /**
     * Get existing entity or null
     *
     * @param $fullClassName string Full classname(with namespace) of the entity.
     *                       e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return null|NotificationEntityInterface
     */
    protected function getEntityObject(string $fullClassName)
    {
        return $this->entityManager->getRepository($fullClassName)->findOneBy(
            [$this->entityIdFieldName => $this->notificationData['id']]
        );
    }

    /**
     * Check if the conditions violate the expectations
     *
     * @param        $entityObject
     * @param string $fullClassName
     * @param string $method
     *
     * @throws NotificationException
     */
    public function checkLogicalViolations(
        $entityObject,
        string $fullClassName,
        string $method
    ) {
        if ($entityObject === null && $method === 'DELETE') {
            throw new NotificationException(
                "Trying to delete entity of class $fullClassName with id " . $this->notificationData['id']
                . ' but the entity does not exist'
            );
        }

        if ($entityObject !== null && $method === 'POST') {
            throw new NotificationException(
                "Trying to create entity of class $fullClassName with id " . $this->notificationData['id'] .
                ' but entity with the id already exists'
            );
        }
    }
}
