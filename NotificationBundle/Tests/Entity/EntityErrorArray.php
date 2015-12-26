<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="error")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityInterfaceErrorArray implements NotificationEntityInterface
{
    /** @return ClientInterface[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
