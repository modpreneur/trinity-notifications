<?php
/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;



/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\Entity
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 */
class Product
{

    /** @var  int */
    private $id = 1;

    /** @var  string */
    private $name = "Someone's name";

    /** @var  string */
    private $description = "Lorem impsu";



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
}