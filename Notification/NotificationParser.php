<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Event\BeforeParseNotificationEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Exception;
use Trinity\NotificationBundle\Exception\HashMismatchException;


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

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string TestClient secret */
    protected $clientSecret;

    /** @var array Array of request data */
    protected $notificationData;

    // The set method is not called on the entity for
    /** @var array Fields with special meaning. The set method is not called on the entity for those fields. */
    protected $ignoredFields = ["timestamp", "hash", "notification_oauth_client_id", "entityName"];

    /** @var string Field name which will be mapped to the id from the notification request */
    protected $entityIdFieldName;

    /** @var  bool */
    protected $isClient;


    /**
     * NotificationParser constructor.
     * @param LoggerInterface $logger
     * @param EntityConverter $entityConverter
     * @param EventDispatcherInterface $eventDispatcher
     * @param $entityIdFieldName
     * @param $isClient
     */
    public function __construct(
        LoggerInterface $logger,
        EntityConverter $entityConverter,
        EventDispatcherInterface $eventDispatcher,
        $entityIdFieldName,
        $isClient
    )
    {
        $this->logger = $logger;
        $this->entityConverter = $entityConverter;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationData = [];
        $this->entityIdFieldName = $entityIdFieldName;
        $this->isClient = $isClient;
    }


    /**
     * @param  $data           array  Notification data as named array
     * @param  $fullClassName  string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     * @param  $method         string HTTP method of the request
     *
     * @return null|object Returns changed entity(on new[POST] or update[PUT]) or null on delete[DELETE]
     *
     * @throws HashMismatchException When the hash does not match
     */
    public function parseNotification($data, $fullClassName, $method)
    {
        // If there are listeners for this event, fire it and get the message from it(it allows changing the data, className and method)
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_PARSE_NOTIFICATION)) {
            $beforeParseNotificationEvent = new BeforeParseNotificationEvent($data, $fullClassName, $method);
            /** @var BeforeParseNotificationEvent $beforeParseNotificationEvent */
            $beforeParseNotificationEvent = $this->eventDispatcher->dispatch(Events::BEFORE_PARSE_NOTIFICATION, $beforeParseNotificationEvent);
            $data = $beforeParseNotificationEvent->getData();
            $fullClassName = $beforeParseNotificationEvent->getClassname();
            $method = $beforeParseNotificationEvent->getHttpMethod();
        }

        $this->notificationData = $data;

        $method = strtoupper($method);

        //get existing entity from database or create a new one
        $entityObject = $this->getEntityObject($fullClassName, $this->entityIdFieldName);

        if ($method == "POST" || $method == "PUT") {
            $this->logger->info("METHOD: POST||PUT:" . $method);

            $entityObject = $this->entityConverter->performEntityChanges(
                $entityObject,
                $this->notificationData,
                $this->ignoredFields
            );

            $this->entityManager->persist($entityObject);

            return $entityObject;
        } else {
            if ($method == "DELETE") {
                $this->logger->info("METHOD: DELETE " . $method);
                $this->entityManager->remove($entityObject);

                return null;
            } else {
                $this->logger->info("method is not supported" . $method);
            }
        }

        return null;
    }


    /**
     * Get existing entity or create a new one
     *
     * @param $fullClassName string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @param $fieldName string Entity field which will be mapped to field "id" from request
     *
     * @return null|object
     */
    protected function getEntityObject($fullClassName, $fieldName)
    {
        $entityObject = $this->entityManager->getRepository($fullClassName)->findOneBy(
            [$fieldName => $this->notificationData["id"]]
        );

        //set server id
        //only on client
        if ($entityObject && $this->isClient) {
            call_user_func_array([$entityObject, "set" . ucfirst($fieldName)], [$this->notificationData["id"]]);
        }

        if ($entityObject) {
            return $entityObject;
        }

        $entityClass = new \ReflectionClass($fullClassName);
        $entityObject = $entityClass->newInstanceArgs();

        return $entityObject;
    }


    /**
     * @param $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityConverter->setEntityManager($entityManager);
    }
}