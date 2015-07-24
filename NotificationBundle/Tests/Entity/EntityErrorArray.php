<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;



/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\Entity
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="error")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 */
class EntityErrorArray
{

}