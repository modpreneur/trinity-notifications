<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityInterfaceMethodDate implements NotificationEntityInterface
{
    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return new \DateTime('2010-11-12');
    }


    /** @return ClientInterface[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
