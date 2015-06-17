<?php


namespace Trinity\NotificationBundle\Tests\entity;

use Doctrine\ORM\Mapping as ORM;
use Necktie\AppBundle\Entity\Client;
use Trinity\FrameworkBundle\Entity\IEntityNotification;
use Trinity\FrameworkBundle\Notification\Annotations as Notify;



/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\entity
 *
 * @ORM\Table()
 *
 * @Notify\Source(columns="id, name, description")
 * @Notify\Methods(types={"put", "post", "delete"})
 *
 */
class TestEntity implements  IEntityNotification{


    /** @return int */
    public function getId()
    {
        return 1;
    }



    /** @return Client|Client[] */
    public function getClients()
    {
        $c = new Client();
        $c->setEnableNotification(true);


        return [$c];
    }
}