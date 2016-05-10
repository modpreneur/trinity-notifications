<?php
namespace Trinity\NotificationBundle\AppTests\Sandbox\Entity;
use Trinity\NotificationBundle\Drivers\ApiDriver\ApiClientInterface;


/**
 * Class TestClient
 * @package Trinity\NotificationBundle\AppTests\Sandbox\Entity
 */
class Client implements ApiClientInterface
{

    /** @return string */
    public function getNotificationUri()
    {
        return "http://127.0.0.1:8001";
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


    /** @return string */
    public function getName()
    {
        return 'sandbox';
    }


    /** @return int */
    public function getId()
    {
        return 1;
    }
}