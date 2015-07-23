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

        private $id;

        private $name;

        private $description;



        /** @return int */
        public function getId()
        {
            return 1;
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

            $c = new Client();
            $c->setEnableNotification(true);

            return [$c];
        }
    }