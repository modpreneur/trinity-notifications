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
    const DEFAULT_TTL = 30;

    /** @var  string[] */
    protected $statuses;

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
    public function __construct( $id)
    {
        parent::__construct();

        $this->statuses = [self::STATUS_ERROR, self::STATUS_OK, self::STATUS_SENT, self::STATUS_UNKNOWN];
        $this->id = $id;
        $this->status = self::STATUS_UNKNOWN;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId( $id)
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isIncoming()
    {
        return $this->incoming;
    }

    /**
     * @param bool $incoming
     */
    public function setIncoming( $incoming)
    {
        $this->incoming = $incoming;
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
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus( $status)
    {
        if (!in_array($status, $this->statuses, true)) {
            throw new \InvalidArgumentException(
                'Notification status has to be one of: '.implode(', ', $this->statuses).'. Given:'.$status
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
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param string $extra
     */
    public function setExtra( $extra)
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
    public static function getLogName()
    {
        return self::TYPE;
    }

    /**
     * Return a default tll in days.
     *
     * @return int
     */
    public static function getDefaultTtl()
    {
        return self::DEFAULT_TTL;
    }
}
