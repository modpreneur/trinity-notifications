<?php

namespace Trinity\NotificationBundle\AppTests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\ClientInterface as CI;
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


    /** @return CI[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }


    /**
     * @param CI $client
     * @param string $status
     * @return void
     */
    public function setSyncStatus(CI $client, $status)
    {
        // TODO: Implement setSyncStatus() method.
    }
}
