<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Trinity\FrameworkBundle\Entity\ClientInterface as CI;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityInterfaceWithoutClient implements NotificationEntityInterface
{
    /** @var  int */
    private $id;

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
        return "Someone's name";
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Lorem impsu';
    }


    /** @return TestClient[] */
    public function getClients()
    {
        return [];
    }


    /**
     * @param CI $client
     * @param string $status
     * @return void
     */
    public function setSyncStatus(CI $client, $status)
    {
        // TODO: Implement setSyncStatus() method.
    }
}
