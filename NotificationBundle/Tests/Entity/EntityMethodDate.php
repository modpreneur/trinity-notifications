<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\INotificationEntity;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityMethodDate implements INotificationEntity
{
    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return new \DateTime('2010-11-12');
    }


    /** @return IClient[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
