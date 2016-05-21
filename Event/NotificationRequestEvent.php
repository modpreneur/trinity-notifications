<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 15:54
 */

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\Message;

/**
 * Class NotificationRequestEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class NotificationRequestEvent extends NotificationEvent
{
    /** @var  Message */
    protected $message;


    /**
     * NotificationRequestEvent constructor.
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
}
