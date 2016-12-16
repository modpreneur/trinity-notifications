<?php

namespace Trinity\NotificationBundle\Entity;

/**
 * Class NotificationLog.
 */
class NotificationLog extends Notification
{
    const TYPE = 'NotificationLog';

    const STATUS_OK = NotificationStatus::STATUS_OK;
    const STATUS_ERROR = NotificationStatus::STATUS_ERROR;
    const STATUS_SENT = NotificationStatus::STATUS_SENT;
    const STATUS_UNKNOWN = 'unknown';
    const STATUSES = [self::STATUS_ERROR, self::STATUS_OK, self::STATUS_SENT, self::STATUS_UNKNOWN];

    /** @var string */
    protected $id;

    /** @var int */
    protected $ttl = 0;

    /** @var  bool */
    protected $incoming = false;

    /** @var  string */
    protected $status;

    /** @var  string */
    protected $statusMessage = '';

    /** @var string */
    protected $extra = '';

    /**
     * NotificationLog constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct();

        $this->id = $id;
        $this->status = self::STATUS_UNKNOWN;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     */
    public function setTtl(int $ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return bool
     */
    public function isIncoming(): bool
    {
        return $this->incoming;
    }

    /**
     * @param bool $incoming
     */
    public function setIncoming(bool $incoming)
    {
        $this->incoming = $incoming;
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
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus(string $status)
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException(
                'Notification status has to be one of: '.implode(', ', self::STATUSES).'. Given:'.$status
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
    public function getExtra(): string
    {
        return $this->extra;
    }

    /**
     * @param string $extra
     */
    public function setExtra(string $extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }

    /**
     * @param Notification $notification
     *
     * @return NotificationLog
     */
    public static function createFromNotification(Notification $notification)
    {
        $log = new self('');
        $log->messageId = $notification->messageId;
        $log->data = json_encode($notification->data);
        $log->entityName = $notification->entityName;
        $log->entityId = $notification->entityId;
        $log->changeSet = json_encode($notification->changeSet);
        $log->method = $notification->method;
        $log->isForced = $notification->isForced;
        $log->createdAt = $notification->createdAt;
        $log->clientId = $notification->clientId;
        $log->uid = $notification->uid;

        return $log;
    }
}
