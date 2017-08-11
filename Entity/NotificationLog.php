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
    const DEFAULT_TTL = 30;

    /** @var string */
    protected $id;

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

    /**
     * Return a human readable string containing only characters.
     * For example: ExceptionLog, IpnLog
     *
     * @return string
     */
    public static function getLogName(): string
    {
        return self::TYPE;
    }

    /**
     * Return a default tll in days.
     *
     * @return int
     */
    public static function getDefaultTtl(): int
    {
        return self::DEFAULT_TTL;
    }
}
