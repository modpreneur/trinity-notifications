<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 26.11.15
 * Time: 16:28
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
}