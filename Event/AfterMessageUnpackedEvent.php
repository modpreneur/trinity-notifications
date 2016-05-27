<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 23.05.16
 * Time: 11:15
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\NotificationBundle\Entity\Message;

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


    /**
     * AfterMessageUnpackedEvent constructor.
     *
     * @param Message    $messageObject
     * @param string     $messageJson
     * @param \Exception $exception
     */
    public function __construct(Message $messageObject = null, string $messageJson = null, \Exception $exception = null)
    {
        $this->messageObject = $messageObject;
        $this->messageJson = $messageJson;
        $this->exception = $exception;
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
}
