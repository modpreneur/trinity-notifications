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


    /**
     * @var bool Was the event processed by any listener?
     */
    protected $eventProcessed = false;

    
    /**
     * MessageReadEvent constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }


    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @param Message $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }


    /**
     * @return boolean
     */
    public function isEventProcessed()
    {
        return $this->eventProcessed;
    }


    /**
     * @param boolean $eventProcessed
     */
    public function setEventProcessed($eventProcessed)
    {
        $this->eventProcessed = $eventProcessed;
    }
}
