<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:52
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class BeforeMessageReadEvent
 */
class BeforeMessageReadEvent extends NotificationEvent
{
    /** @var string */
    protected $message;


    /**
     * BeforeMessageReadEvent constructor.
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