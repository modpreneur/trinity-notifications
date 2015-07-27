<?php
/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\FrameworkBundle\Entity\IClient;



/**
 *
 * @package Trinity\NotificationBundle\Event
 */
class StatusEvent extends Event
{

    const NULL_MESSAGE = "No messages";

    /** @var IClient */
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



    /**
     * @param $client
     * @param $entityName
     * @param $entityId
     * @param $url
     * @param $json
     * @param $method
     * @param \Exception|null $exception
     * @param string $message
     */
    public function __construct(
        $client,
        $entityName,
        $entityId,
        $url,
        $json,
        $method,
        \Exception $exception = null,
        $message = self::NULL_MESSAGE
    ){
        $this->exception = $exception;
        $this->entityName = $entityName;
        $this->method = $method;
        $this->url = $url;
        $this->json = $json;
        $this->client = $client;
        $this->entityId = $entityId;

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
     * @return IClient
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


}