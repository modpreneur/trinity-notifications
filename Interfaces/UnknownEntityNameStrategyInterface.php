<?php

namespace Trinity\NotificationBundle\Interfaces;

use Trinity\NotificationBundle\Entity\Notification;

/**
 * Interface UnknownEntityNameHandlerInterface
 *
 * @package Trinity\NotificationBundle\Interfaces
 */
interface UnknownEntityNameStrategyInterface
{
    /**
     * This method is called when the system receives a notification with entity name which is not known.
     * This handler could for example perform some custom actions or throw an exception.
     *
     * @param Notification $notification The notification which has the unknown entity name
     *
     * @return bool True if the solver solved the situation. False otherwise
     */
    public function unknownEntityName(Notification $notification);
}
