<?php


namespace Trinity\NotificationBundle\Tests\Sandbox\Entity;


use Trinity\FrameworkBundle\Entity\IClient;


class Client implements IClient
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