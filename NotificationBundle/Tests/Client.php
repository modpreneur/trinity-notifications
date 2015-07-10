<?php
    namespace Trinity\NotificationBundle\Tests;


    use Trinity\FrameworkBundle\Entity\IClient;



    class Client implements IClient
    {

        private $enable;



        /** @return string */
        public function getNotifyUrl()
        {
            return "http://api.dev.clickandcoach.com/notify/";
        }



        public function setEnableNotification($e)
        {
            $this->enable = $e;
        }



        public function isNotificationEnabled()
        {
            return $this->enable;
        }



        public function getSecret()
        {
            return "8rsxbgk63b40k8g0cs00ks0s8s0co0884sk4swgk04s8sk8ck";
        }

    }