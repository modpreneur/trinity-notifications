<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityWithoutSource
{
}
