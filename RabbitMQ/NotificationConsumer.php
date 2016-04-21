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
use Trinity\NotificationBundle\Notification\NotificationReader;

abstract class NotificationConsumer extends Consumer
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  NotificationReader */
    protected $reader;

    /**
     * ServerConsumer constructor.
     *
     * @param BaseRabbitSetup $setup
     * @param NotificationReader $notificationReader
     * @param EventDispatcherInterface $eventDispatcher
     * @param Producer $producer
     */
    public function __construct(
        BaseRabbitSetup $setup,
        NotificationReader $notificationReader,
        EventDispatcherInterface $eventDispatcher,
        Producer $producer = null
    )
    {
        parent::__construct($setup, $producer);

        $this->reader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * Consume message
     *
     * @param Message $message
     *
     * @throws \Exception On any error
     * @return void
     */
    public function consume(Message $message)
    {
        try {
            dump($message->content);
            $this->reader->read($message->content);
        } catch (\Exception $exception) {
            $this->dispatchErrorEvent($exception);

            throw $exception;
        }
    }


    /**
     * @param \Exception $e
     */
    public function dispatchErrorEvent(\Exception $e)
    {
        // If there are listeners for this event, fire it and get the message from it
        //(it allows changing the entityObject, data and ignoredFields)
        if ($this->eventDispatcher->hasListeners(Events::CONSUME_MESSAGE_ERROR)) {
            $consumeMessageErrorEvent = new ConsumeMessageErrorEvent($e);
            /** @var ConsumeMessageErrorEvent $consumeMessageErrorEvent */
            $this->eventDispatcher->dispatch(Events::CONSUME_MESSAGE_ERROR, $consumeMessageErrorEvent);
        }
    }
}