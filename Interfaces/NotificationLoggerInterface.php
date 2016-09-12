<?php

namespace Trinity\NotificationBundle\Interfaces;

use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationStatus;

/**
 * Interface NotificationLoggerInterface.
 */
interface NotificationLoggerInterface
{
    /**
     * @param Notification $notification
     */
    public function logIncomingNotification(Notification $notification);

    /**
     * @param Notification $notification
     */
    public function logOutcomingNotification(Notification $notification);

    /**
     * Set status of the notifications.
     *
     * @param NotificationStatus[] $statuses
     */
    public function setNotificationStatuses(array $statuses);
}
