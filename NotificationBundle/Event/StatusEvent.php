<?php

    namespace Trinity\NotificationBundle\Event;

    use Trinity\NotificationBundle\EventListener\IEntityNotification;
    use Symfony\Component\EventDispatcher\Event;
    use Trinity\FrameworkBundle\Entity\IClient;



    /**
     * Class ErrorEvent
     * @author Tomáš Jančar
     *
     * @package Trinity\NotificationBundle\Event
     */
    class StatusEvent extends Event
    {


        const NULL_MESSAGE = "No message";

        /** @var IClient */
        protected $client;

        /** @var  \Exception */
        protected $exception;

        /** @var  string */
        protected $message;

        /** @var  IEntityNotification */
        protected $entityName;

        /** @var  string */
        protected $url;

        /** @var  string */
        protected $method;

        /** @var  string */
        protected $json;

        /** @var  int */
        protected $entityId;



        function __construct(
            IClient $client,
            $entityName,
            $entityId,
            $url,
            $json,
            $method,
            \Exception $exception = null,
            $message = self::NULL_MESSAGE
        ) {
            $this->exception = $exception;
            $this->entityName = $entityName;
            $this->method = $method;
            $this->url = $url;
            $this->json = $json;
            $this->client = $client;
            $this->entityId = $entityId;

            if ($exception && $message === null) {
                $this->message = $exception->getMessage();
            } else {
                $this->message = $message;
            }
        }



        /**
         * @return \Exception
         */
        public function getException()
        {
            return $this->exception;
        }



        /**
         * @return string
         */
        public function getMessage()
        {
            return $this->message;
        }



        /**
         * @return string
         */
        public function getEntityName()
        {
            return $this->entityName;
        }



        /**
         * @return string
         */
        public function getUrl()
        {
            return $this->url;
        }



        /**
         * @return string
         */
        public function getMethod()
        {
            return $this->method;
        }



        /**
         * @return string
         */
        public function getJson()
        {
            return $this->json;
        }



        /**
         * @return bool
         */
        public function hasError()
        {
            return $this->exception !== null;
        }



        /**
         * @return IClient
         */
        public function getClient()
        {
            return $this->client;
        }



        /**
         * @return int
         */
        public function getEntityId()
        {
            return $this->entityId;
        }



        /**
         * @param int $entityId
         */
        public function setEntityId($entityId)
        {
            $this->entityId = $entityId;
        }


    }