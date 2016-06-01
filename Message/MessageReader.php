<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.05.16
 * Time: 12:39
 */

namespace Trinity\NotificationBundle\Message;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Entity\StatusMessage;
use Trinity\NotificationBundle\Event\AfterMessageUnpackedEvent;
use Trinity\NotificationBundle\Event\BeforeMessageReadEvent;
use Trinity\NotificationBundle\Event\ConsumeMessageErrorEvent;
use Trinity\NotificationBundle\Event\DeadLetteredMessageReadEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\MessageReadEvent;
use Trinity\NotificationBundle\Event\SetMessageStatusEvent;
use Trinity\NotificationBundle\Exception\HashMismatchException;
use Trinity\NotificationBundle\Exception\MessageNotProcessedException;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;

/**
 * Class MessageReader
 *
 * @package Trinity\NotificationBundle\Message
 */
class MessageReader
{
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ClientSecretProviderInterface */
    protected $clientSecretProvider;


    /**
     * MessageReader constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param \Bunny\Message $bunnyMessage
     * @param string         $sourceQueue
     */
    public function read(\Bunny\Message $bunnyMessage, string $sourceQueue)
    {
        try {
            $messageString = $bunnyMessage->content;

            //If there are listeners for this event, fire it and get the message from it(it allows changing the message)
            if ($this->eventDispatcher->hasListeners(Events::BEFORE_MESSAGE_READ)) {
                $beforeReadEvent = new BeforeMessageReadEvent($messageString);
                $beforeReadEvent->setMessage($messageString);
                /** @var BeforeMessageReadEvent $beforeReadEvent */
                $beforeReadEvent = $this->eventDispatcher->dispatch(Events::BEFORE_MESSAGE_READ, $beforeReadEvent);
                $messageString = $beforeReadEvent->getMessage();
            }

            // Try to unpack and log message
            $messageObject = $this->getAndLogMessageObject($messageString, $sourceQueue);

            //now the $messageObject is successfully unpacked
            if ($this->isDeadLettered($bunnyMessage)) {
                $event = new DeadLetteredMessageReadEvent($messageObject, $messageString, $sourceQueue);

                if ($this->eventDispatcher->hasListeners(Events::DEAD_LETTERED_MESSAGE_READ)) {
                    /** @var DeadLetteredMessageReadEvent $event */
                    $event = $this->eventDispatcher->dispatch(Events::DEAD_LETTERED_MESSAGE_READ, $event);
                    $messageObject = $event->getMessage();
                }
            } else {
                $event = new MessageReadEvent($messageObject, $messageString, $sourceQueue);
                if ($this->eventDispatcher->hasListeners(Events::MESSAGE_READ)) {
                    /** @var MessageReadEvent $event */
                    $event = $this->eventDispatcher->dispatch(Events::MESSAGE_READ, $event);
                    $messageObject = $event->getMessage();
                }
            }

            $this->checkIfTheMessageWasProcessed($event, $messageObject);

        } catch (\Exception $exception) {
            $this->dispatchErrorEvent($exception, $bunnyMessage, $sourceQueue);
        }
    }


    /**
     * @param string $messageString
     * @param string $sourceQueue
     *
     * @return Message
     * @throws \Exception
     */
    protected function getAndLogMessageObject(string $messageString, string $sourceQueue)
    {
        try {
            $messageObject = Message::unpack($messageString);
            $this->dispatchAfterMessageUnpackedEvent($messageString, $sourceQueue, $messageObject);

            return $messageObject;
        } catch (\Exception $exception) {
            $this->dispatchAfterMessageUnpackedEvent($messageString, $sourceQueue, null, $exception);

            throw $exception;
        }
    }


    /**
     * @param ClientSecretProviderInterface $clientSecretProvider
     */
    public function setClientSecretProvider(ClientSecretProviderInterface $clientSecretProvider)
    {
        $this->clientSecretProvider = $clientSecretProvider;
    }


    /**
     * Get client secret from the message.
     *
     * @param Message $message
     *
     * @return string
     */
    protected function getClientSecret(Message $message) : string
    {
        return $this->clientSecretProvider->getClientSecret($message->getClientId());
    }


    /**
     * Log message
     *
     * @param string       $messageJson
     * @param string       $sourceQueue
     * @param Message|null $messageObject null when unpacking of the message failed
     * @param \Exception   $exception
     */
    protected function dispatchAfterMessageUnpackedEvent(
        string $messageJson,
        string $sourceQueue,
        Message $messageObject = null,
        \Exception $exception = null
    ) {
        $event = new AfterMessageUnpackedEvent($messageObject, $messageJson, $exception, $sourceQueue);
        if ($this->eventDispatcher->hasListeners(Events::AFTER_MESSAGE_UNPACKED)) {
            /** @var MessageReadEvent $event */
            $this->eventDispatcher->dispatch(Events::AFTER_MESSAGE_UNPACKED, $event);
        }
    }


    /**
     * @param Message $message
     * @param string  $status
     * @param string  $statusMessage
     */
    protected function setMessageStatus(Message $message, string $status, string $statusMessage)
    {
        $event = new SetMessageStatusEvent($message, $status, $statusMessage);
        if ($this->eventDispatcher->hasListeners(Events::SET_MESSAGE_STATUS)) {
            /** @var MessageReadEvent $event */
            $this->eventDispatcher->dispatch(Events::SET_MESSAGE_STATUS, $event);
        }
    }

    /**
     * Check if the message was dead lettered = has "x-death" header
     *
     * @see https://www.rabbitmq.com/dlx.html
     *
     * @param \Bunny\Message $bunnyMessage
     *
     * @return bool
     */
    protected function isDeadLettered(\Bunny\Message $bunnyMessage) : bool
    {
        return array_key_exists('x-death', $bunnyMessage->headers);
    }


    /**
     * @param Message $messageObject
     *
     * @throws HashMismatchException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     */
    protected function checkHash(Message $messageObject)
    {
        $messageObject->setClientSecret($this->clientSecretProvider->getClientSecret($messageObject->getClientId()));

        if (!$messageObject->isHashValid()) {
            throw new HashMismatchException('The message hash is not valid');
        }
    }


    /**
     * @param MessageReadEvent $event
     * @param Message          $message
     *
     * @throws MessageNotProcessedException
     */
    protected function checkIfTheMessageWasProcessed(MessageReadEvent $event, Message $message)
    {
        if ($event === null || !$event->isEventProcessed()) {
            $exception = new MessageNotProcessedException(
                'The given message was not processed by any event!. Message data: ' . $message->getJsonData()
            );
            $exception->setMessageObject($message);

            $this->setMessageStatus($message, StatusMessage::STATUS_ERROR, $exception->getMessage());

            throw $exception;
        }
    }


    /**
     * @param \Exception             $exception
     * @param \Bunny\Message|Message $message
     * @param string                 $sourceQueue
     */
    public function dispatchErrorEvent(\Exception $exception, \Bunny\Message $message, string $sourceQueue)
    {
        // If there are listeners for this event, fire it and get the message from it
        //(it allows changing the entityObject, data and ignoredFields)
        if ($this->eventDispatcher->hasListeners(Events::CONSUME_MESSAGE_ERROR)) {
            $event = new ConsumeMessageErrorEvent($exception, $message, $sourceQueue);
            /** @var ConsumeMessageErrorEvent $event */
            $this->eventDispatcher->dispatch(Events::CONSUME_MESSAGE_ERROR, $event);
        }
    }
}

