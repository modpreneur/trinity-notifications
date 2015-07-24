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
    private $id;

    /** @var  string */
    private $name;

    /** @var  string */
    private $description;



    /** @return int */
    public function getId()
    {
        return 1;
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
        return "Lorem impsu";
    }



    /** @return Client[] */
    public function getClients()
    {

        $c = new Client();
        $c->setEnableNotification(true);

        return [$c];
    }
}