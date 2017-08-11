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
    const STATUSES = [ self::NOT_SYNCHRONIZED, self::SYNCHRONIZATION_IN_PROGRESS, self::SYNCHRONIZATION_ERROR,
        self::SYNCHRONIZED, self::UNKNOWN];

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
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     */
    public function setClientId(int $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getMessageUid(): string
    {
        return $this->messageUid;
    }

    /**
     * @param string $messageUid
     */
    public function setMessageUid(string $messageUid)
    {
        $this->messageUid = $messageUid;
    }

    /**
     * @return int
     */
    public function getChangedAt(): int
    {
        return $this->changedAt;
    }

    /**
     * @param int $changedAt
     */
    public function setChangedAt(int $changedAt)
    {
        $this->changedAt = $changedAt;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @throws \InvalidArgumentException
     */
    public function setStatus(string $status)
    {
        if (!in_array($status, self::STATUSES, true)
        ) {
            throw new \InvalidArgumentException(
                "Status '$status' is not valid. Choose one from '". implode(',', self::STATUSES)
            );
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    /**
     * @param string $statusMessage
     */
    public function setStatusMessage(string $statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }

    /**
     * @return string
     */
    public function getNotificationId(): string
    {
        return $this->notificationId;
    }

    /**
     * @param string $notificationId
     */
    public function setNotificationId(string $notificationId)
    {
        $this->notificationId = $notificationId;
    }
}
