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
        ClientInterface $client,
        string $entityName,
        int $entityId,
        string $url,
        string $json,
        string $method,
        \Exception $exception = null,
        string $message = self::NULL_MESSAGE,
        BaseUser $user = null
    ) {
        $this->exception = $exception;
        $this->entityName = $entityName;
        $this->method = $method;
        $this->url = $url;
        $this->json = $json;
        $this->client = $client;
        $this->entityId = $entityId;
        $this->user = $user;
        $this->message = $message;

        if ($exception !== null && $message === null) {
            $this->message = $exception->getMessage();
        }
    }


    /**
     * @return \Exception
     */
    public function getException() : \Exception
    {
        return $this->exception;
    }


    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }


    /**
     * @return string
     */
    public function getEntityName() : string
    {
        return $this->entityName;
    }


    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }


    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }


    /**
     * @return string
     */
    public function getJson() : string
    {
        return $this->json;
    }


    /**
     * @return bool
     */
    public function hasError() : bool
    {
        return $this->exception !== null;
    }


    /**
     * @return ClientInterface
     */
    public function getClient() : ClientInterface
    {
        return $this->client;
    }


    /**
     * @return int
     */
    public function getEntityId() : int
    {
        return $this->entityId;
    }


    /**
     * @param int $entityId
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;
    }


    /**
     * @return BaseUser
     */
    public function getUser() : BaseUser
    {
        return $this->user;
    }


    /**
     * @param BaseUser $user
     */
    public function setUser(BaseUser $user)
    {
        $this->user = $user;
    }
}

