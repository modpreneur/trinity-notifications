<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\NotificationBundle\Annotations as Notification;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="error")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityErrorArray
{
}
