<?php
    namespace Trinity\NotificationBundle\Entity;

    //grid

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use Doctrine\Common\Collections\ArrayCollection;
    use APY\DataGridBundle\Grid\Mapping as GRID;

    /**
     * @ORM\Entity
     * @UniqueEntity("name")
     * @GRID\Source(columns="id, name, command, delay, created, execute")
     */
    class CronTask
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(type="string")
         * @GRID\Column(title="Name")
         */
        private $name;

        /**
         * @ORM\Column(type="array")
         * @GRID\Column(type="array", title="Command")
         */
        private $command;

        /**
         * @ORM\Column(type="integer")
         * @GRID\Column(title="Delay")
         */
        private $delay;

        /**
         * @ORM\Column(type="datetime")
         * @GRID\Column(title="Created")
         */
        private $created;

        /**
         * @ORM\Column(type="datetime", nullable=true)
         * @GRID\Column(title="Execute")
         */
        private $execute;




        public function getId()
        {
            return $this->id;
        }



        public function getName()
        {
            return $this->name;
        }



        public function setName($name)
        {
            $this->name = $name;
            return $this;
        }



        public function getCommand()
        {
            return $this->command;
        }



        public function setCommand($command)
        {
            $this->command = $command;
            return $this;
        }



        public function getDelay()
        {
            return $this->delay;
        }



        public function setDelay($delay)
        {
            $this->delay = $delay;
            return $this;
        }



        /**
         * @return mixed
         */
        public function getCreated()
        {
            return $this->created;
        }



        /**
         * @param mixed $created
         * @return $this
         */
        public function setCreated($created)
        {
            $this->created = $created;
            return $this;
        }



        /**
         * @return mixed
         */
        public function getExecute()
        {
            return $this->execute;
        }



        /**
         * @param mixed $execute
         */
        public function setExecute($execute)
        {
            $this->execute = $execute;
        }

    }