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
    protected $notified;

    /** @var  string */
    protected $secret;


    /** @var string */
    protected $name;

    /** @var  int */
    protected $id;


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
    public function isNotified()
    {
        return $this->notified;
    }


    /**
     * @param boolean $notified
     */
    public function setNotified($notified)
    {
        $this->notified = $notified;
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


    /** @return string */
    public function getName()
    {
        return $this->name;
    }


    /** @return int */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}