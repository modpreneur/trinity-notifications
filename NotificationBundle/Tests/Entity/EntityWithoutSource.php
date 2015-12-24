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
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityInterfaceWithoutSource implements NotificationEntityInterface
{
    /** @return ClientInterface[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
