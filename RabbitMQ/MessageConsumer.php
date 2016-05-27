<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 15:46
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\BunnyBundle\Consumer\Consumer;
use Trinity\Bundle\BunnyBundle\Producer\Producer;
use Trinity\Bundle\BunnyBundle\Setup\BaseRabbitSetup;
use Trinity\NotificationBundle\Event\ConsumeMessageErrorEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Message\MessageReader;

/**
 * Class MessageConsumer
 */
abstract class MessageConsumer extends Consumer
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  MessageReader */
    protected $reader;

    /**
     * ServerConsumer constructor.
     *
     * @param BaseRabbitSetup          $setup
     * @param MessageReader            $notificationReader
     * @param EventDispatcherInterface $eventDispatcher
     * @param Producer                 $producer
     */
    public function __construct(
        BaseRabbitSetup $setup,
        MessageReader $notificationReader,
        EventDispatcherInterface $eventDispatcher,
        Producer $producer = null
    ) {
        parent::__construct($setup, $producer);

        $this->reader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * Consume message
     *
     * @param Message $message
     *
     * @param string  $sourceQueue
     *
     * @throws \Exception On any error
     */
    public function consume(Message $message, string $sourceQueue)
    {
        try {
            dump("Reading from queue '$sourceQueue': " . $message->content);
            $this->reader->read($message, $sourceQueue);
        } catch (\Exception $exception) {
            $this->dispatchErrorEvent($exception, $message);

            throw $exception;
        }
    }


    /**
     * @param \Exception $exception
     * @param Message    $message
     */
    public function dispatchErrorEvent(\Exception $exception, Message $message)
    {
        // If there are listeners for this event, fire it and get the message from it
        //(it allows changing the entityObject, data and ignoredFields)
        if ($this->eventDispatcher->hasListeners(Events::CONSUME_MESSAGE_ERROR)) {
            $event = new ConsumeMessageErrorEvent($exception, $message);
            /** @var ConsumeMessageErrorEvent $event */
            $this->eventDispatcher->dispatch(Events::CONSUME_MESSAGE_ERROR, $event);
        }
    }
}