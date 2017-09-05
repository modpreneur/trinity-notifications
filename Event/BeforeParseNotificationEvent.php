<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:51.
 */
namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\Notification;

/**
 * Class BeforeParseNotificationEvent.
 */
class BeforeParseNotificationEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.beforeParseNotification';

    /** @var Notification */
    protected $notification;

    /** @var string */
    protected $classname;

    /** @var  bool True will skip the notification process */
    protected $ignoreNotification = false;

    /**
     * BeforeParseNotificationEvent constructor.
     *
     * @param Notification $notification
     * @param string       $classname
     */
    public function __construct(Notification $notification, string $classname)
    {
        $this->notification = $notification;
        $this->classname = $classname;
    }

    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    /**
     * @param string $classname
     */
    public function setClassname(string $classname)
    {
        $this->classname = $classname;
    }

    /**
     * @return bool
     */
    public function isIgnoreNotification(): bool
    {
        return $this->ignoreNotification;
    }

    /**
     * @param bool $ignoreNotification
     */
    public function setIgnoreNotification(bool $ignoreNotification)
    {
        $this->ignoreNotification = $ignoreNotification;
    }
}
