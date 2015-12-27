<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Client;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description, tProduct")
 * @Notification\DependentSources(columns="id")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class Product implements NotificationEntityInterface
{
    /** @var  int */
    private $id;

    /** @var  string */
    private $name = "Someone's name";

    /** @var  string */
    private $description = 'Lorem impsu';

    /** @var  EEntityInterface */
    private $tProduct;

    private $status = [];


    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->tProduct = new EEntityInterface();
        $this->id = rand(10, 999999);
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


    /** @return ClientInterface[] */
    public function getClients()
    {
        $c = new Client();

        return [$c];
    }


    /**
     * @return EEntityInterface
     */
    public function getTProduct()
    {
        return $this->tProduct;
    }


    /**
     * @param EEntityInterface $tProduct
     */
    public function setTProduct($tProduct)
    {
        $this->tProduct = $tProduct;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @param ClientInterface $client
     * @param string $status
     * @return void
     */
    public function setSyncStatus(ClientInterface $client, $status)
    {
        $this->status[$client->getName()] = $status;
    }
}
