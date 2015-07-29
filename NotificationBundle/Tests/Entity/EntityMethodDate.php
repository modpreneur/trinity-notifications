<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityMethodDate
{
    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return new \DateTime('2010-11-12');
    }
}
