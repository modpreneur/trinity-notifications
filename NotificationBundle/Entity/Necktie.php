<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\FrameworkBundle\Entity\IClient;

class Necktie implements IClient
{
    protected $notificationUri;

    public function setNotificationUri($uri)
    {
        $this->notificationUri = $uri;
    }

    /** @return string */
    public function getNotificationUri()
    {
        return $this->notificationUri;
    }


    /** @return  boolean */
    public function isNotificationEnabled()
    {
        // TODO: Implement isNotificationEnabled() method.
    }


    /** @return string */
    public function getSecret()
    {
        // TODO: Implement getSecret() method.
    }
}