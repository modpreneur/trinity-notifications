<?php

namespace Trinity\NotificationBundle\Tests\Entity;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityWithoutClient
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



    /** @return Client[] */
    public function getClients()
    {
        return [];
    }
}
