<?php


namespace Trinity\NotificationBundle\Tests\Sandbox\Entity;


use Trinity\FrameworkBundle\Entity\ClientInterface;


class Client implements ClientInterface
{

    /** @return string */
    public function getNotificationUri()
    {
        return "http://127.0.0.1:8000";
    }


    /** @return  boolean */
    public function isNotificationEnabled()
    {
        return true;
    }


    /** @return string */
    public function getSecret()
    {
        return "secretKey";
    }
}