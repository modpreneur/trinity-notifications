<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\INotificationEntity;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityDisableClient implements INotificationEntity
{
    /** @var  int */
    private $id = 1;

    /** @var  string */
    private $name;

    /** @var  string */
    private $description;


    /** @return int */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'Disable Client';
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Disable client description.';
    }


    /** @return Client[] */
    public function getClients()
    {
        $c = new Client();
        $c->setEnableNotification(false);

        return [$c];
    }
}
