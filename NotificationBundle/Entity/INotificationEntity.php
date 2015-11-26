<?php


namespace Trinity\NotificationBundle\Entity;

use Trinity\FrameworkBundle\Entity\IClient;


/**
 * Interface INotificationEntity
 *
 * @package Trinity\NotificationBundle\Entity
 */
interface INotificationEntity
{
    /** @return IClient[] */
    public function getClients();

}