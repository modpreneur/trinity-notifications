<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\FrameworkBundle\Entity\BaseUser;
use Trinity\FrameworkBundle\Entity\ClientInterface;


/**
 *
 */
class StatusEvent extends Event
{
    const NULL_MESSAGE = 'No messages';

    /** @var ClientInterface */
    protected $client;

    /** @var  \Exception */
    protected $exception;

    /** @var  string */
    protected $message;

    /** @var  string */
    protected $entityName;

    /** @var  string */
    protected $url;

    /** @var  string */
    protected $method;

    /** @var  string */
    protected $json;

    /** @var  int */
    protected $entityId;

    /** @var BaseUser */
    protected $user;


    /**
     * @param ClientInterface $client
     * @param string $entityName
     * @param int $entityId
     * @param string $url
     * @param string $json
     * @param string $method
     * @param \Exception|null $exception
     * @param string $message
     * @param BaseUser $user
     */
    public function __construct(
        $client,
        $entityName,
        $entityId,
        $url,
        $json,
        $method,
        \Exception $exception = null,
        $message = self::NULL_MESSAGE,
        $user = null
    ) {
        $this->exception = $exception;
        $this->entityName = $entityName;
        $this->method = $method;
        $this->url = $url;
        $this->json = $json;
        $this->client = $client;
        $this->entityId = $entityId;
        $this->user = $user;

        if ($exception && $message === null) {
            $this->message = $exception->getMessage();
        } else {
            $this->message = $message;
        }
    }


    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }


    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->exception !== null;
    }


    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }


    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }


    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }


    /**
     * @return BaseUser
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @param BaseUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}
