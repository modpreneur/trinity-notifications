<?php

namespace Trinity\NotificationBundle\Notification;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException;
use Trinity\NotificationBundle\Exception\NotificationConstraintException;

class NotificationConstraints
{
    /** @var  NotificationEntityInterface|null */
    protected $entityObject;

    /** @var  string */
    protected $fullClassName;

    /** @var  string */
    protected $method;

    /** @var int */
    protected $entityId;

    /** @var  bool */
    protected $isClient;

    /** @var bool */
    protected $disableTimeViolations = true;

    /**
     * NotificationConstraints constructor.
     *
     * @param bool $isClient
     * @param bool $disableTimeViolations
     */
    public function __construct(bool $isClient, bool $disableTimeViolations)
    {
        $this->isClient = $isClient;
        $this->disableTimeViolations = $disableTimeViolations;
    }

    /**
     * @param NotificationEntityInterface|null $entityObject
     * @param string                           $fullClassName
     * @param string                           $method
     * @param int                              $entityId
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationConstraintException
     */
    public function checkLogicalViolations(
        NotificationEntityInterface $entityObject = null,
        string $fullClassName,
        string $method,
        int $entityId
    ) {
        $this->entityObject = $entityObject;
        $this->fullClassName = $fullClassName;
        $this->method = $method;
        $this->entityId = $entityId;

        $this->deleteNullObject();
        $this->entityExists();
        $this->deleteOnServer();
        $this->createOnServer();
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param $notificationCreatedAt
     *
     * @throws EntityWasUpdatedBeforeException
     */
    public function checkTimeViolations(NotificationEntityInterface $entity = null, $notificationCreatedAt)
    {
        if (!$this->disableTimeViolations && $entity !== null && $entity->getUpdatedAt() > $notificationCreatedAt) {
            throw new EntityWasUpdatedBeforeException(
                'The entity of class "'.get_class($entity).
                '" has been updated after the notification message was created'
            );
        }
    }

    /**
     * @param NotificationEntityInterface|null $entity
     * @param string $HTTPMethod
     *
     * @throws NotificationConstraintException
     */
    public function unexpectedObjectAndMethodCombination(NotificationEntityInterface $entity = null, string $HTTPMethod)
    {
        throw new NotificationConstraintException(
            "Unsupported combination of input conditions. Tried to apply method $HTTPMethod on ".
            ($entity ? 'existing' : 'non existing').' entity. '.
            'This may be because creation of entities on server is prohibited.'
        );
    }

    /**
     * @throws NotificationConstraintException
     */
    protected function deleteNullObject()
    {
        if ($this->entityObject === null && $this->method === 'DELETE') {
            throw new NotificationConstraintException(
                "Trying to delete entity of class {$this->fullClassName} with id ".$this->entityId
                .' but the entity does not exist'
            );
        }
    }

    /**
     * @throws NotificationConstraintException
     */
    protected function entityExists()
    {
        if ($this->entityObject !== null && $this->method === 'POST') {
            throw new NotificationConstraintException(
                "Trying to create entity of class {$this->fullClassName} with id ".$this->entityId.
                ' but entity with the id already exists'
            );
        }
    }

    /**
     * @throws NotificationConstraintException
     */
    protected function deleteOnServer()
    {
        //allow deleting entities only on client
        if ($this->entityObject !== null && $this->method === 'DELETE' && !$this->isClient) {
            throw new NotificationConstraintException(
                "Trying to delete entity of class {$this->fullClassName} with id ".$this->entityId
                .' but it is not allowed on the server.'
            );
        }
    }

    /**
     * @throws NotificationConstraintException
     */
    protected function createOnServer()
    {
        //allow creating entities only on client
        if ($this->entityObject === null && ($this->method === 'POST' || $this->method === 'PUT') && !$this->isClient) {
            throw new NotificationConstraintException(
                "Trying to create entity of class {$this->fullClassName} with id ".$this->entityId
                .' but it is not allowed on the server.'
            );
        }
    }
}
