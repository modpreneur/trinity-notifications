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
use Trinity\NotificationBundle\Event\BeforeMessageReadEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\MessageReadEvent;
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
    /**
     * @var  EventDispatcherInterface
     */
    protected $eventDispatcher;


    /**
     * @var ClientSecretProviderInterface
     */
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
     * @param string $message
     *
     * @throws \Exception
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\NotificationBundle\Exception\MessageNotProcessedException
     * @throws \Trinity\NotificationBundle\Exception\HashMismatchException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     */
    public function read(string $message)
    {
        // If there are listeners for this event, fire it and get the message from it(it allows changing the message)
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_MESSAGE_READ)) {
            $beforeReadEvent = new BeforeMessageReadEvent($message);
            $beforeReadEvent->setMessage($message);
            /** @var BeforeMessageReadEvent $beforeReadEvent */
            $beforeReadEvent = $this->eventDispatcher->dispatch(Events::BEFORE_MESSAGE_READ, $beforeReadEvent);
            $message = $beforeReadEvent->getMessage();
        }

        // Log message - either only json or json and Message object
        $messageObject = null;
        try {
            $messageObject = Message::unpack($message);
            $this->logMessage($message, $messageObject);
        } catch (\Exception $e) {
            $this->logMessage($message);

            throw $e;
        }

        $messageObject->setClientSecret($this->clientSecretProvider->getClientSecret($messageObject->getClientId()));

        if (!$messageObject->isHashValid()) {
            throw new HashMismatchException('The message hash is not valid');
        }

        $event = new MessageReadEvent($messageObject);
        if ($this->eventDispatcher->hasListeners(Events::MESSAGE_READ)) {
            /** @var MessageReadEvent $event */
            $event = $this->eventDispatcher->dispatch(Events::MESSAGE_READ, $event);
            $messageObject = $event->getMessage();
        }

        if ($event === null || !$event->isEventProcessed()) {
            $exception = new MessageNotProcessedException(
                'The given message was not processed by any event!. Message data: ' . $message
            );
            $exception->setMessageObject($messageObject);

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


    protected function logMessage(string $messageJson, Message $messageObject = null)
    {
        //todo: log message
    }
}
