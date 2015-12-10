<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\FrameworkBundle\Entity\IClient;

class Master implements IClient
{
    protected $notificationUri;

    protected $isNotificationEnabled;

    protected $secret;


    public function setNotificationUri($uri)
    {
        $this->notificationUri = $uri;
    }


    /** @return string */
    public function getNotificationUri()
    {
        return $this->notificationUri;
    }


    public function isNotificationEnabled()
    {
        return $this->isNotificationEnabled;
    }


    public function setIsNotificationEnabled($isNotificationEnabled)
    {
        $this->isNotificationEnabled = $isNotificationEnabled;
    }


    public function getSecret()
    {
        return $this->secret;
    }


    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
}