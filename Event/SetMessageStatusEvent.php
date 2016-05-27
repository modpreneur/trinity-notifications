<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 23.05.16
 * Time: 13:52
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\NotificationBundle\Entity\Message;

/**
 * Class SetMessageStatusEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class SetMessageStatusEvent extends Event
{
    /** @var  Message */
    protected $message;


    /** @var  string */
    protected $status;


    /**
     * SetMessageStatusEvent constructor.
     *
     * @param Message $message
     * @param string  $status
     */
    public function __construct(Message $message, string $status)
    {
        $this->message = $message;
        $this->status = $status;
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
    public function getStatus() : string
    {
        return $this->status;
    }


    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
