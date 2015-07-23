<?php
namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\DateTime;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;



/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\Entity
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="date")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 */
class EntityMethodDate
{

    public function getDate(){
        return new \DateTime('2010-11-12');
    }

}