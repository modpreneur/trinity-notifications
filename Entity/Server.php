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
    /** @var  boolean */
    protected $notified;

    /** @var  string */
    protected $secret;


    /** @var string */
    protected $name;

    /** @var  int */
    protected $id;


    /**
     * @return bool
     */
    public function isNotified() : bool
    {
        return $this->notified;
    }


    /**
     * @param boolean $notified
     */
    public function setNotified(bool $notified)
    {
        $this->notified = $notified;
    }


    /**
     * @return string
     */
    public function getSecret() : string
    {
        return $this->secret;
    }


    /**
     * @param string $secret
     */
    public function setSecret($secret) : string
    {
        $this->secret = $secret;
    }


    /** @return string */
    public function getName() : string
    {
        return $this->name;
    }


    /** @return int */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }


    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /** @return string */
    public function getNotificationUri() : string
    {
        return '';
    }
}