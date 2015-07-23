<?php


namespace Trinity\NotificationBundle\Tests\Entity;

/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\Entity
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 */
class EntityWithoutClient
{

    private $id;

    private $name;

    private $description;



    /** @return int */
    public function getId()
    {
        return $this->id;
    }



    public function getName()
    {
        return "Someone's name";
    }



    public function getDescription()
    {
        return "Lorem impsu";
    }



    /** @return Client[] */
    public function getClients()
    {
        return [];
    }

}