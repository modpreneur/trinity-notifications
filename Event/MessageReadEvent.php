<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.05.16
 * Time: 12:47
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\NotificationBundle\Entity\Message;

/**
 * Class MessageReadEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class MessageReadEvent extends Event
{
    /** @var  Message */
    protected $message;


    /** @var  string */
    protected $messageJson;


    /** @var  string */
    protected $sourceQueue;


    /**
     * @var bool Was the event processed by any listener?
     */
    protected $eventProcessed = false;


    /**
     * MessageReadEvent constructor.
     *
     * @param Message $message
     * @param string  $messageJson
     * @param string  $sourceQueue
     */
    public function __construct(Message $message, string $messageJson, string $sourceQueue)
    {
        $this->message = $message;
        $this->messageJson = $messageJson;
        $this->sourceQueue = $sourceQueue;
    }


    /**
     * @return string
     */
    public function getMessageJson()
    {
        return $this->messageJson;
    }


    /**
     * @param string $messageJson
     */
    public function setMessageJson($messageJson)
    {
        $this->messageJson = $messageJson;
    }


    /**
     * @return Message
     */
    public function getMessage() : Message
    {
        return $this->message;
    }


    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }


    /**
     * @return string
     */
    public function getSourceQueue() : string
    {
        return $this->sourceQueue;
    }


    /**
     * @param string $sourceQueue
     */
    public function setSourceQueue(string $sourceQueue)
    {
        $this->sourceQueue = $sourceQueue;
    }


    /**
     * @return boolean
     */
    public function isEventProcessed() : bool
    {
        return $this->eventProcessed;
    }


    /**
     * @param boolean $eventProcessed
     */
    public function setEventProcessed(bool $eventProcessed)
    {
        $this->eventProcessed = $eventProcessed;
    }
}

