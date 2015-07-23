<?php

    namespace Trinity\NotificationBundle\Tests\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Trinity\AnnotationsBundle\Annotations\Notification as Notification;

    /**
     * Class TestEntity
     * @package Trinity\NotificationBundle\Tests\Entity
     *
     * @ORM\Entity()
     *
     * @Notification\Source(columns="*")
     * @Notification\Methods(types={"put", "post"})
     *
     */
    class AllSourceEntity
    {

        private $id = 1;

        private $name = "All source";

        private $description = "Description text.";

        private $price = "10$";



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

    }