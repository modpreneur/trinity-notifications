<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 23.05.16
 * Time: 11:15
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class AfterMessageUnpackedEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class AfterMessageUnpackedEvent extends Event
{
    /** @var  Message */
    protected $messageObject;

    /** @var string */
    protected $messageJson = '';

    /** @var  \Exception */
    protected $exception;

    /** @var  string */
    protected $sourceQueue;


    /**
     * AfterMessageUnpackedEvent constructor.
     *
     * @param Message    $messageObject
     * @param string     $messageJson
     * @param \Exception $exception
     * @param string     $sourceQueue
     */
    public function __construct(
        Message $messageObject = null,
        string $messageJson = null,
        \Exception $exception = null,
        string $sourceQueue = ''
    ) {
        $this->messageObject = $messageObject;
        $this->messageJson = $messageJson;
        $this->exception = $exception;
        $this->sourceQueue = $sourceQueue;
    }


    /**
     * @return Message
     */
    public function getMessageObject() : Message
    {
        return $this->messageObject;
    }


    /**
     * @param Message $messageObject
     */
    public function setMessageObject(Message $messageObject)
    {
        $this->messageObject = $messageObject;
    }


    /**
     * @return string
     */
    public function getMessageJson() : string
    {
        return $this->messageJson;
    }


    /**
     * @param string $messageJson
     */
    public function setMessageJson(string $messageJson)
    {
        $this->messageJson = $messageJson;
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
