<?php

namespace Trinity\NotificationBundle\Entity;

/**
 * Class EntityStatus.
 */
class EntityStatus
{
    const NOT_SYNCHRONIZED = 'NOT_SYNCHRONIZED';
    const SYNCHRONIZATION_IN_PROGRESS = 'SYNCHRONIZATION_IN_PROGRESS';
    const SYNCHRONIZATION_ERROR = 'SYNCHRONIZATION_ERROR';
    const SYNCHRONIZED = 'SYNCHRONIZED';
    const UNKNOWN = 'UNKNOWN';

    /** @var  array */
    protected $statuses = [];

    /** @var  string */
    protected $entityClass;

    /** @var  int */
    protected $entityId;

    /** @var  int */
    protected $clientId;

    /** @var  string */
    protected $messageUid;

    /** @var  string */
    protected $notificationId;

    /** @var  int Timestamp*/
    protected $changedAt;

    /** @var  string */
    protected $status;

    /** @var  string String which holds an additional info */
    protected $statusMessage;

    /**
     * EntityStatus constructor.
     */
    public function __construct()
    {
        $this->statuses = [self::NOT_SYNCHRONIZED, self::SYNCHRONIZATION_IN_PROGRESS, self::SYNCHRONIZATION_ERROR,
            self::SYNCHRONIZED, self::UNKNOWN];
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass( $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getMessageUid()
    {
        return $this->messageUid;
    }

    /**
     * @param string $messageUid
     */
    public function setMessageUid( $messageUid)
    {
        $this->messageUid = $messageUid;
    }

    /**
     * @return int
     */
    public function getChangedAt()
    {
        return $this->changedAt;
    }

    /**
     * @param int $changedAt
     */
    public function setChangedAt($changedAt)
    {
        $this->changedAt = $changedAt;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @throws \InvalidArgumentException
     */
    public function setStatus( $status)
    {
        if (!in_array($status, $this->statuses, true)
        ) {
            throw new \InvalidArgumentException(
                "Status '$status' is not valid. Choose one from '". implode(',', $this->statuses)
            );
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @param string $statusMessage
     */
    public function setStatusMessage( $statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }

    /**
     * @return string
     */
    public function getNotificationId()
    {
        return $this->notificationId;
    }

    /**
     * @param string $notificationId
     */
    public function setNotificationId( $notificationId)
    {
        $this->notificationId = $notificationId;
    }
}
