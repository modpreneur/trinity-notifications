<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:52
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class BeforeReadMessageEvent
 */
class BeforeReadMessageEvent extends NotificationEvent
{
    /** @var string */
    protected $message;


    /**
     * BeforeReadMessageEvent constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }


    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }


    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

}