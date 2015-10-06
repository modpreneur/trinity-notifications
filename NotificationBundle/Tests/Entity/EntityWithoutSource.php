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
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityWithoutSource implements INotificationEntity
{
    /** @return IClient[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
