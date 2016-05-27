<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:44
 */

namespace Trinity\NotificationBundle\Event;

use Bunny\Message;

/**
 * Class ConsumeMessageErrorEvent
 */
class ConsumeMessageErrorEvent extends NotificationEvent
{
    /** @var  \Exception */
    protected $exception;


    /** @var  Message */
    protected $message;


    /**
     * ConsumeMessageErrorEvent constructor.
     *
     * @param \Exception $exception
     * @param Message    $message
     */
    public function __construct(\Exception $exception, Message $message)
    {
        $this->exception = $exception;
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

    /**
     * @return \Exception
     */
    public function getException() : \Exception
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }


}