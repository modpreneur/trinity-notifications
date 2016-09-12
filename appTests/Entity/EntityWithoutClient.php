<?php

namespace Trinity\NotificationBundle\AppTests\Entity;

use Trinity\Component\Core\Interfaces\ClientInterface as CI;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityWithoutClient implements NotificationEntityInterface
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

    /** @return CI[] */
    public function getClients()
    {
        return [];
    }

    /**
     * @param CI     $client
     * @param string $status
     */
    public function setSyncStatus(CI $client, $status)
    {
        // TODO: Implement setSyncStatus() method.
    }
}
