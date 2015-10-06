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
 * @Notification\Source(columns="error")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityErrorArray implements INotificationEntity
{
    /** @return IClient[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
