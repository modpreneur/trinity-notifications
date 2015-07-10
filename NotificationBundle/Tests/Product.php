<?php
    /*
     * This file is part of the Trinity project.
     *
     */

    namespace Trinity\NotificationBundle\Tests;


    use Doctrine\ORM\Mapping as ORM;
    use Trinity\FrameworkBundle\Notification\Annotations as Notification;


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
    class Product {


        /** @return int */
        public function getId() {
            return 1;
        }


        /** @return Client[] */
        public function getClients() {

            $c = new Client();
            $c->setEnableNotification( true );

            return [ $c ];
        }
}