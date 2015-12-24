<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\FrameworkBundle\Entity\ClientInterface;


/**
 * Class Server
 *
 * @package Trinity\NotificationBundle\Entity
 */
class Server implements ClientInterface
{
    /** @var  string */
    protected $notificationUri;

    /** @var  boolean */
    protected $isNotificationEnabled;

    /** @var  string */
    protected $secret;


    /** @return string */
    public function getNotificationUri()
    {
        return $this->notificationUri;
    }


    /**
     * @param string $uri
     */
    public function setNotificationUri($uri)
    {
        $this->notificationUri = $uri;
    }


    /**
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return $this->isNotificationEnabled;
    }


    /**
     * @param boolean $isNotificationEnabled
     */
    public function setIsNotificationEnabled($isNotificationEnabled)
    {
        $this->isNotificationEnabled = $isNotificationEnabled;
    }


    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }


    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

}