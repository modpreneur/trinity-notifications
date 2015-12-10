<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;
use Trinity\FrameworkBundle\Entity\IClient;


/**
 * Class Master
 *
 * @package Trinity\NotificationBundle\Entity
 */
class Master implements IClient
{
    /** @var  string */
    protected $notificationUri;

    /** @var  boolean */
    protected $isNotificationEnabled;

    /** @var  string */
    protected $secret;


    /**
     * @param string $uri
     */
    public function setNotificationUri($uri)
    {
        $this->notificationUri = $uri;
    }


    /** @return string */
    public function getNotificationUri()
    {
        return $this->notificationUri;
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