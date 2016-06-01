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

    /** @var  string */
    protected $sourceQueue;


    /**
     * ConsumeMessageErrorEvent constructor.
     *
     * @param \Exception $exception
     * @param Message    $message
     * @param string     $sourceQueue
     */
    public function __construct(\Exception $exception, Message $message, string $sourceQueue)
    {
        $this->exception = $exception;
        $this->message = $message;
        $this->sourceQueue = $sourceQueue;
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

    /**
     * @return string
     */
    public function getSourceQueue()
    {
        return $this->sourceQueue;
    }

    /**
     * @param string $sourceQueue
     */
    public function setSourceQueue($sourceQueue)
    {
        $this->sourceQueue = $sourceQueue;
    }
}
