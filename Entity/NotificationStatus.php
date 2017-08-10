<?php

namespace Trinity\NotificationBundle\Entity;

/**
 * Class NotificationStatus
 */
class NotificationStatus
{
    const NOTIFICATION_ID_KEY = 'notificationId';
    const STATUS_KEY = 'status';
    const MESSAGE_KEY = 'message';
    const EXTRA_KEY = 'extra';

    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';
    const STATUS_SENT = 'sent';
    const STATUSES = [self::STATUS_OK, self::STATUS_ERROR, self::STATUS_SENT];

    /** @var  string */
    protected $notificationId = '';

    /** @var  string */
    protected $status = '';

    /** @var  string */
    protected $message = '';

    /** @var  array */
    protected $extra = [];

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
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException(
                'Notification status has to be one of: '.implode(', ', self::STATUSES)
            );
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage( $message)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::NOTIFICATION_ID_KEY => $this->notificationId,
            self::STATUS_KEY => $this->status,
            self::MESSAGE_KEY => $this->message,
            self::EXTRA_KEY => $this->extra,
        ];
    }

    /**
     * Create Notification from array.
     *
     * @param array $array
     *
     * @return NotificationStatus
     */
    public static function fromArray(array $array = [])
    {
        $notificationObject = new self();

        $notificationObject->notificationId = $array[self::NOTIFICATION_ID_KEY];
        $notificationObject->status = $array[self::STATUS_KEY];
        $notificationObject->message = $array[self::MESSAGE_KEY];
        $notificationObject->extra = $array[self::EXTRA_KEY];

        return $notificationObject;
    }
}
