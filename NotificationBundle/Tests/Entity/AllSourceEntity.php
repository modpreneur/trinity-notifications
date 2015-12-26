<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="*")
 * @Notification\Methods(types={"put", "post"})
 */
class AllSourceEntityInterface implements NotificationEntityInterface
{
    /** @var int */
    private $id = 1;

    /** @var string */
    private $name = 'All source';

    /** @var string */
    private $description = 'Description text.';

    /** @var string */
    private $price = '10$';


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }


    /** @return ClientInterface[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }
}
