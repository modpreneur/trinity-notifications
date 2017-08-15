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
use Trinity\NotificationBundle\Event\BeforeDeleteEntityEvent;
use Trinity\NotificationBundle\Event\BeforeParseNotificationEvent;
use Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\UnexpectedEntityStateException;
use Trinity\NotificationBundle\Interfaces\DatabaseEntityFetcherInterface;
use Trinity\NotificationBundle\Interfaces\UnknownEntityNameStrategyInterface;

/**
 * Responsible for parsing notification request and performing entity edits.
 *
 * Class NotificationParser
 */
class NotificationParser
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var  EntityConversionHandler */
    protected $conversionHandler;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  EntityAssociator */
    protected $entityAssociator;

    /** @var  UnknownEntityNameStrategyInterface[] */
    protected $unknownEntityStrategies = [];

    /** @var  DatabaseEntityFetcherInterface */
    protected $databaseEntityFetcher;

    /** @var array Array of request data */
    protected $notificationData;

    /** @var  bool */
    protected $isClient;

    /** @var UnexpectedEntityStateException[] */
    protected $notificationExceptions = [];

    /**
     * @var array Indexed array of entities' aliases and real class names.
     *            format:
     *            [
     *            "user" => "App\Entity\User,
     *            "product" => "App\Entity\Product,
     *            ....
     *            ]
     */
    protected $entities;

    /** @var bool */
    protected $disableTimeViolations = true;

    /**
     * NotificationParser constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityConversionHandler $conversionHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @param EntityAssociator $entityAssociator
     * @param bool $isClient
     * @param array $entities
     * @param bool $disableTimeViolations
     */
    public function __construct(
        LoggerInterface $logger,
        EntityConversionHandler $conversionHandler,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        EntityAssociator $entityAssociator,
        DatabaseEntityFetcherInterface $databaseEntityFetcher,
        bool $isClient,
        array $entities,
        bool $disableTimeViolations = true
    ) {
        $this->logger = $logger;
        $this->conversionHandler = $conversionHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->entityAssociator = $entityAssociator;
        $this->databaseEntityFetcher = $databaseEntityFetcher;
        $this->notificationData = [];
        $this->isClient = $isClient;
        $this->entities = $entities;
        $this->disableTimeViolations = $disableTimeViolations;
    }

    /**
     * @param UnknownEntityNameStrategyInterface $strategy
     */
    public function addUnknownEntityStrategy(UnknownEntityNameStrategyInterface $strategy)
    {
        if (!in_array($strategy, $this->unknownEntityStrategies, true)) {
            $this->unknownEntityStrategies[] = $strategy;
        }
    }

    /**
     * @param Notification[] $notifications
     *
     * @return array
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws NotificationException
     */
    public function parseNotifications(array $notifications) : array
    {
        $processedEntities = [];

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $entityName = $notification->getEntityName();

            if (!array_key_exists($entityName, $this->entities)) {
                if (true === $this->solveUnknownEntityName($notification)) {
                    continue;
                } else {
                    throw new NotificationException(
                        'No strategy was able to solve the unknown "'.$notification->getEntityName().'" entity name'
                    );
                }
            }

            $processedEntity = null;

            try {
                $processedEntity = $this->parseNotification($notification, $this->entities[$entityName]);
            } catch (UnexpectedEntityStateException $exception) {
                $exception->setNotification($notification);
                $this->notificationExceptions[] = $exception;
            }

            if ($processedEntity !== null) {
                $processedEntities[] = $processedEntity;
            }
        }

        $this->entityAssociator->associate($processedEntities);

        return $processedEntities;
    }

    /**
     * @param Notification $notification
     * @param              $fullClassName string Full className(with namespace) of the entity. e.g.
     *                                    AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return null|object
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     *
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws NotificationException
     */
    public function parseNotification(Notification $notification, string $fullClassName)
    {
        // If there are listeners for this event,
        // fire it and get the message from it(it allows changing the data, className and method)
        if ($this->eventDispatcher->hasListeners(BeforeParseNotificationEvent::NAME)) {
            $event = new BeforeParseNotificationEvent($notification, $fullClassName);
            /* @var BeforeParseNotificationEvent $event */
            $this->eventDispatcher->dispatch(
                BeforeParseNotificationEvent::NAME,
                $event
            );
        }

        $HTTPMethod = strtoupper($notification->getMethod());
        $this->notificationData = $notification->getData();

        //get existing entity from database or null
        $entityObject = $this->getEntityObject($fullClassName);

        /*
        exist  && delete   - delete entity, without form
        !exist && delete   - throw exception -> error message
        exist  && post     - throw exception -> error message
        !exist && post     - use form to create an entity from notification
        !exist && put      - use form to create an entity from notification
        exist  && put      - use form to edit the entity with notification data
        */

        //check if there are specific conditions which are strictly prohibited
        $this->checkLogicalViolations($entityObject, $fullClassName, $HTTPMethod);

        if ($notification->getCreatedAt() !== null) {
            if (!$notification->isForced()) {
                $this->checkTimeViolations(
                    $entityObject,
                    (new \DateTime())->setTimestamp($notification->getCreatedAt())
                );
            }
        }

        //this whole if-else block is non-optimal but quite readable
        //delete entity, without form
        if ($entityObject !== null && $HTTPMethod === 'DELETE') {
            $this->logger->info('METHOD: DELETE '.$HTTPMethod);
            /** @var BeforeDeleteEntityEvent $event */
            $event = $this->eventDispatcher->dispatch(
                BeforeDeleteEntityEvent::NAME,
                new BeforeDeleteEntityEvent($entityObject)
            );
            $entityObject = $event->getEntity();

            $this->entityManager->remove($entityObject);

            return;
        } elseif ($entityObject === null && $HTTPMethod === 'POST') {
            return $this->conversionHandler->performEntityCreate(
                array_search($fullClassName, $this->entities, true),
                $notification->getData()
            );
        } elseif ($entityObject === null && $HTTPMethod === 'PUT') {
            return $this->conversionHandler->performEntityCreate(
                array_search($fullClassName, $this->entities, true),
                $notification->getData()
            );
        } elseif ($entityObject !== null && $HTTPMethod === 'PUT') {
            return $this->conversionHandler->performEntityUpdate(
                $entityObject,
                $notification->getData(),
                $notification->getChangeSet(),
                $notification->isForced()
            );
            //other strange combination of input conditions
        } else {
            throw new NotificationException(
                "Unsupported combination of input conditions. Tried to apply method $HTTPMethod on ".
                ($entityObject ? 'existing' : 'non existing').' entity. '
            );
        }
    }

    /**
     * @return \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException[]
     */
    public function getNotificationExceptions(): array
    {
        return $this->notificationExceptions;
    }

    /**
     * Get existing entity or null.
     *
     * @param $fullClassName string Full className(with namespace) of the entity.
     *                       e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return null|NotificationEntityInterface
     */
    protected function getEntityObject(string $fullClassName)
    {
        return $this->databaseEntityFetcher->fetchEntity($fullClassName, $this->notificationData);
    }

    /**
     * Check if the conditions violate the expectations.
     *
     * @param        $entityObject
     * @param string $fullClassName
     * @param string $method
     *
     * @throws NotificationException
     */
    public function checkLogicalViolations($entityObject, string $fullClassName, string $method) {
        if ($entityObject === null && $method === 'DELETE') {
            throw new NotificationException(
                "Trying to delete entity of class $fullClassName with id ".$this->notificationData['id']
                .' but the entity does not exist'
            );
        }

        if ($entityObject !== null && $method === 'POST') {
            throw new NotificationException(
                "Trying to create entity of class $fullClassName with id ".$this->notificationData['id'].
                ' but entity with the id already exists'
            );
        }

        //allow deleting entities only on client
        if ($entityObject !== null && $method === 'DELETE' && !$this->isClient) {
            throw new NotificationException(
                "Trying to delete entity of class $fullClassName with id ".$this->notificationData['id']
                .' but it is not allowed on the server.'
            );
        }

        //allow creating entities only on client
        if ($entityObject === null && $method === 'POST' && !$this->isClient) {
            throw new NotificationException(
                "Trying to create entity of class $fullClassName with id ".$this->notificationData['id']
                .' but it is not allowed on the server.'
            );
        }

        //allow creating entities only on client
        if ($entityObject === null && $method === 'PUT' && !$this->isClient) {
            throw new NotificationException(
                "Trying to create(PUT has the same effect as POST on non-existing entity now)' . 
                ' entity of class $fullClassName with id ".$this->notificationData['id']
                .' but it is not allowed on the server.'
            );
        }
    }

    /**
     * @param NotificationEntityInterface | null $entity
     * @param \DateTime                          $notificationCreatedAt
     *
     * @throws EntityWasUpdatedBeforeException
     */
    protected function checkTimeViolations($entity, \DateTime $notificationCreatedAt)
    {
        if (!$this->disableTimeViolations && $entity !== null && $entity->getUpdatedAt() > $notificationCreatedAt) {
            throw new EntityWasUpdatedBeforeException(
                'The entity of class "'.get_class($entity).
                '" has been updated after the notification message was created'
            );
        }
    }

    /**
     * @param Notification $notification
     * @return bool True if the situation was solved, false otherwise
     */
    protected function solveUnknownEntityName(Notification $notification)
    {
        foreach ($this->unknownEntityStrategies as $unknownEntityStrategy) {
            //if solved, stop executing the strategies
            if (true === $unknownEntityStrategy->unknownEntityName($notification)) {
                return true;
            }
        }

        return false;
    }
}
