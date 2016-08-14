<?php

namespace Trinity\NotificationBundle\Entity;

use Trinity\Bundle\LoggerBundle\Entity\BaseElasticLog;

/**
 * Class EntityStatusLog.
 */
class EntityStatusLog extends BaseElasticLog
{
    const TYPE = 'EntityStatusLog';

    const NOT_SYNCHRONIZED = 'NOT_SYNCHRONIZED';
    const SYNCHRONIZATION_IN_PROGRESS = 'SYNCHRONIZATION_IN_PROGRESS';
    const SYNCHRONIZATION_ERROR = 'SYNCHRONIZATION_ERROR';
    const SYNCHRONIZED = 'SYNCHRONIZED';

    /** @var  string */
    protected $entityClass;

    /** @var  int */
    protected $entityId;

    /** @var  int */
    protected $clientId;

    /** @var  string */
    protected $messageUid;

    /** @var  int Timestamp*/
    protected $changedAt;

    /** @var  string */
    protected $status;

    /**
     * EntityStatusLog constructor.
     *
     * @param string $id
     */
    public function __construct($id = '')
    {
        parent::__construct($id);

        $this->clientId = '';
        $this->messageUid = '';
        $this->changedAt = time();
        $this->createdAt = time();
        $this->status = self::NOT_SYNCHRONIZED;
    }

    /**
     * @return int
     */
    public function getClientId() : int
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
    public function getMessageUid() : string
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
    public function getChangedAt() : int
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
    public function getEntityClass() : string
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
    public function getEntityId() : int
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
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
