<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 23.05.16
 * Time: 14:36
 */

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\Message;

/**
 * Class BeforeMessagePublish
 *
 * @package Trinity\NotificationBundle\Event
 */
class BeforeMessagePublish
{
    /** @var  Message */
    protected $message;


    /**
     * BeforeMessagePublish constructor.
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
}
