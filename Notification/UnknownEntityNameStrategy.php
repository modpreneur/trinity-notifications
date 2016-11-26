<?php

namespace Trinity\NotificationBundle\Notification;

use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Interfaces\UnknownEntityNameStrategyInterface;

/**
 * Class UnknownEntityNameStrategy
 */
class UnknownEntityNameStrategy implements UnknownEntityNameStrategyInterface
{
    /**
     * This method is called when the system receives a notification with entity name which is not known.
     * This handler could for example perform some custom actions or throw an exception.
     *
     * @param Notification $notification The notification which has the unknown entity name
     *
     * @return void nothing as the function only throws a exception
     * @throws NotificationException
     */
    public function unknownEntityName(Notification $notification)
    {
        throw new NotificationException(
            "No classname found for entityName: '{$notification->getEntityName()}'.".
            ' Have you defined it in the configuration under trinity_notification:entities?'
        );
    }
}