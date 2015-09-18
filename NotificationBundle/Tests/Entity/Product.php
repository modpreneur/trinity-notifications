<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\NotificationBundle\Annotations as Notification;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description, tProduct")
 * @Notification\DependentSources(columns="id")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class Product
{
    /** @var  int */
    private $id = 1;

    /** @var  string */
    private $name = "Someone's name";

    /** @var  string */
    private $description = 'Lorem impsu';

    /** @var  EEntity */
    private $tProduct;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->tProduct = new EEntity();
    }

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
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /** @return Client[] */
    public function getClients()
    {
        $c = new Client();
        $c->setEnableNotification(true);

        return [$c];
    }

    /**
     * @return EEntity
     */
    public function getTProduct()
    {
        return $this->tProduct;
    }

    /**
     * @param EEntity $tProduct
     */
    public function setTProduct($tProduct)
    {
        $this->tProduct = $tProduct;
    }
}
