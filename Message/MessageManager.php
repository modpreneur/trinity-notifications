<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 23.05.16
 * Time: 14:34
 */

namespace Trinity\NotificationBundle\Message;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\BunnyBundle\Producer\Producer;
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Event\BeforeMessagePublish;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\RabbitMQ\NotificationProducer;

/**
 * Class MessageManager
 *
 * @package Trinity\NotificationBundle\Notification
 */
class MessageManager
{
    /** @var Message[] */
    protected $messages = [];

    /** @var NotificationProducer */
    protected $producer;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;


    /**
     * MessageManager constructor.
     *
     * @param NotificationProducer     $producer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(NotificationProducer $producer, EventDispatcherInterface $eventDispatcher)
    {
        $this->producer = $producer;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @return Producer
     */
    public function getProducer() : Producer
    {
        return $this->producer;
    }


    /**
     * This method is called in driver.
     *
     * @param Producer $producer
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;
    }


    /**
     * Send all messages to the rabbit.
     *
     * @param string $messageType
     */
    public function send(string $messageType = 'notification')
    {
        foreach ($this->messages as $message) {
            $this->sendMessage($message, $messageType);
        }

        $this->clear();
    }


    /**
     * Send one message
     *
     * @param Message $message
     * @param string  $messageType
     *
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     */
    public function sendMessage(Message $message, string $messageType)
    {
        $hasListeners = $this->eventDispatcher->hasListeners(Events::BEFORE_MESSAGE_PUBLISH);

        if ($hasListeners) {
            $event = new BeforeMessagePublish($message);
            /** @var BeforeMessagePublish $event */
            $event = $this->eventDispatcher->dispatch(Events::BEFORE_MESSAGE_PUBLISH, $event);
            $message = $event->getMessage();
        }

        $this->producer->publish(
            $message->pack(),
            ($messageType === 'notification') ?
                $this->producer->getRabbitSetup()->getOutputNotificationsExchange() :
                $this->producer->getRabbitSetup()->getOutputMessagesExchangeName(),
            $message->getClientId()
        );
    }


    /**
     * @return Message[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }


    /**
     * @param Message[] $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }


    /**
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }


    /**
     * Clear the inner array to prevent sending the notifications twice
     */
    public function clear()
    {
        $this->messages = [];
    }
}
