<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\UnexpectedEntityStateException;
use Trinity\NotificationBundle\Services\EntityAliasTranslator;

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

    /** @var  NotificationEventDispatcher */
    protected $eventDispatcher;

    /** @var array Array of request data */
    protected $notificationData;

    /** @var string Field name which will be mapped to the id from the notification request */
    protected $entityIdFieldName;

    /** @var UnexpectedEntityStateException[] */
    protected $notificationExceptions = [];

    /** @var  NotificationConstraints */
    protected $constraints;

    /** @var  EntityAliasTranslator */
    protected $entityAliasTranslator;

    /**
     * NotificationParser constructor.
     * 
     * @param LoggerInterface             $logger
     * @param EntityConversionHandler     $conversionHandler
     * @param NotificationEventDispatcher $eventDispatcher
     * @param EntityManagerInterface      $entityManager
     * @param EntityAssociator            $entityAssociator
     * @param EntityAliasTranslator       $entityAliasTranslator
     * @param string                      $entityIdFieldName
     */
    public function __construct(
        LoggerInterface $logger,
        EntityConversionHandler $conversionHandler,
        NotificationEventDispatcher $eventDispatcher,
        EntityManagerInterface $entityManager,
        EntityAssociator $entityAssociator,
        EntityAliasTranslator $entityAliasTranslator,
        string $entityIdFieldName
    ) {
        $this->logger = $logger;
        $this->conversionHandler = $conversionHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->entityAssociator = $entityAssociator;
        $this->entityAliasTranslator = $entityAliasTranslator;
        $this->entityIdFieldName = $entityIdFieldName;
        $this->notificationData = [];
    }

    /**
     * @param Notification[] $notifications
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws NotificationException
     *
     * @return array
     */
    public function parseNotifications(array $notifications) : array
    {
        $processedEntities = [];
        $this->notificationExceptions = [];

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $processedEntity = null;

            try {
                $processedEntity = $this->parseNotification($notification);
            } catch (UnexpectedEntityStateException $exception) {
                $exception->setNotification($notification);
                $this->notificationExceptions[] = $exception;
            }

            //neither error nor deleted
            if ($processedEntity !== null) {
                $processedEntities[] = $processedEntity;
            }
        }

        return $processedEntities;
    }

    /**
     * @param Notification $notification
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     *
     * @return null|NotificationEntityInterface
     * @throws \Trinity\NotificationBundle\Exception\NotificationConstraintException
     */
    protected function parseNotification(Notification $notification)
    {
        $this->notificationData = $notification->getData();
        $entityName = $notification->getEntityName();
        $entityClass = $this->entityAliasTranslator->getClassFromAlias($entityName);

        $this->eventDispatcher->dispatchBeforeParseNotificationEvent($notification, $entityClass);
        $HTTPMethod = strtoupper($notification->getMethod());

        //get existing entity from database or null
        $entityObject = $this->getEntityObject($entityName);

        //check if there are specific conditions which are strictly prohibited
        $this->checkLogicalViolations($entityObject, $entityClass, $HTTPMethod);
        $this->checkTimeViolations($notification, $entityObject);

        /*
        exist  && delete   - delete entity, without form
        !exist && delete   - throw exception -> error message
        exist  && post     - throw exception -> error message
        !exist && post     - use form to create an entity from notification
        !exist && put      - use form to create an entity from notification
        exist  && put      - use form to edit the entity with notification data
        */
        //this whole if-else block is non-optimal but quite readable
        //delete entity, without form
        if ($entityObject !== null && $HTTPMethod === 'DELETE') {
            $this->deleteEntity($entityObject);
        } elseif ($entityObject === null && $HTTPMethod === 'POST') {
            return $this->createEntity($notification);
        } elseif ($entityObject === null && $HTTPMethod === 'PUT') {
            return $this->createEntity($notification);
        } elseif ($entityObject !== null && $HTTPMethod === 'PUT') {
            return $this->updateEntity($entityObject, $notification);
            //other strange combination of input conditions
        } else {
            $this->constraints->unexpectedObjectAndMethodCombination($entityObject, $HTTPMethod);
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
     * @param NotificationEntityInterface $entity
     * @param Notification                $notification
     *
     * @return NotificationEntityInterface
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function updateEntity(NotificationEntityInterface $entity, Notification $notification)
    {
        return $this->conversionHandler->performEntityUpdate(
            $entity,
            $notification->getEntityName(),
            $notification->getData(),
            $notification->getChangeSet(),
            $notification->isForced()
        );
    }

    /**
     * @param Notification $notification
     *
     * @return NotificationEntityInterface
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function createEntity(Notification $notification)
    {
        return $this->conversionHandler->performEntityCreate(
            $notification->getEntityName(),
            $notification->getData()
        );
    }

    /**
     * @param NotificationEntityInterface $entityObject
     */
    protected function deleteEntity(NotificationEntityInterface $entityObject)
    {
        $this->eventDispatcher->dispatchBeforeDeleteEntityEvent($entityObject);
        $this->entityManager->remove($entityObject);
    }

    /**
     * Get existing entity or null.
     *
     * @param $entityName string
     *
     * @return null|NotificationEntityInterface
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     */
    protected function getEntityObject(string $entityName)
    {
        $fullClassName = $this->entityAliasTranslator->getClassFromAlias($entityName);
        /** @var NotificationEntityInterface|null $entity */
        $entity = $this->entityManager->getRepository($fullClassName)->findOneBy(
            [$this->entityIdFieldName => $this->notificationData['id']]
        );

        return $entity;
    }

    /**
     * Check if the conditions violate the expectations.
     *
     * @param NotificationEntityInterface $entityObject
     * @param string                      $fullClassName
     * @param string                      $method
     *
     * @throws NotificationException
     */
    public function checkLogicalViolations(
        NotificationEntityInterface $entityObject = null,
        string $fullClassName,
        string $method
    ) {
        $this->constraints->checkLogicalViolations(
            $entityObject,
            $fullClassName,
            $method,
            $this->notificationData['id']
        );
    }

    /**
     * @param Notification                     $notification
     * @param NotificationEntityInterface|null $entity
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     */
    protected function checkTimeViolations(Notification $notification, NotificationEntityInterface $entity)
    {
        if ($notification->getCreatedAt() !== null && !$notification->isForced()) {
            $this->constraints->checkTimeViolations(
                $entity,
                (new \DateTime())->setTimestamp($notification->getCreatedAt())
            );
        }
    }
}
