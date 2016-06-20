<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 14.04.16
 * Time: 8:44
 */

namespace Trinity\NotificationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\MessagesBundle\Event\Events as MessagesEvents;
use Trinity\Bundle\MessagesBundle\Event\ReadMessageEvent;
use Trinity\Bundle\MessagesBundle\Event\SetMessageStatusEvent;
use Trinity\Bundle\MessagesBundle\Event\StatusMessageEvent;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationRequestMessage;
use Trinity\NotificationBundle\Event\Events as NotificationsEvents;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Notification\NotificationManager;
use Trinity\NotificationBundle\Notification\NotificationReader;

/**
 * Class NotificationEventsListener
 *
 * If the messaging protocol will be decoupled from notification bundle this class would be moved out of this bundle.
 * The messaging protocol does not need to know all the types of messages.
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class NotificationEventsListener
{
    /** @var  NotificationReader */
    protected $notificationReader;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  NotificationManager */
    protected $notificationManager;

    /** @var  bool */
    protected $isClient;


    /**
     * NotificationEventsListener constructor.
     *
     * @param NotificationReader       $notificationReader
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationManager      $notificationManager
     * @param bool                     $isClient
     */
    public function __construct(
        NotificationReader $notificationReader,
        EventDispatcherInterface $eventDispatcher,
        NotificationManager $notificationManager,
        bool $isClient
    ) {
        $this->notificationReader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationManager = $notificationManager;
        $this->isClient = $isClient;
    }


    /**
     * @param ReadMessageEvent $event
     *
     * @throws \Exception
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     */
    public function onMessageRead(ReadMessageEvent $event)
    {
        $message = $event->getMessage();

        /*
         * If the message type was recognized handle it.
         * If not just let it be because another listener may recognize it
         */
        if ($message->getType() === NotificationBatch::MESSAGE_TYPE) {
            $this->handleNotificationMessage($message);
            $this->setEventAsProcessed($event);

        } elseif ($message->getType() === NotificationRequestMessage::MESSAGE_TYPE) {
            $this->handleNotificationRequest($message);
            $this->setEventAsProcessed($event);
        } elseif ($message->getType() === StatusMessage::MESSAGE_TYPE) {
            $this->handleStatusMessage($message);
            $this->setEventAsProcessed($event);
        }
    }


    /**
     * @param SendNotificationEvent $event
     */
    public function onSendNotificationEvent(SendNotificationEvent $event)
    {
        $this->notificationManager->queueEntity(
            $event->getEntity(),
            $event->getMethod(),
            !$this->isClient,
            $event->getOptions()
        );
    }


    /**
     * @param Message $message
     *
     * @return array
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Exception
     *
     * @throws \Throwable Catches all catchable errors and exceptiond and then throws it again
     *
     */
    protected function handleNotificationMessage(Message $message)
    {
        try {
            $entities = $this->notificationReader->read($message);
            $this->dispatchSetMessageStatusEvent(
                $message,
                StatusMessage::STATUS_OK,
                'ok'
            );

            return $entities;
        } catch (\Throwable $exception) {
            $this->dispatchSetMessageStatusEvent(
                $message,
                StatusMessage::STATUS_ERROR,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    /**
     * @param Message $message
     */
    protected function handleNotificationRequest(Message $message)
    {
        $event = new NotificationRequestEvent($message);
        if ($this->eventDispatcher->hasListeners(NotificationsEvents::NOTIFICATION_REQUEST)) {
            /** @var NotificationRequestEvent $event */
            $this->eventDispatcher->dispatch(NotificationsEvents::NOTIFICATION_REQUEST, $event);
        }
    }


    /**
     * @param Message $message
     */
    protected function handleStatusMessage(Message $message)
    {
        $statusMessage = StatusMessage::createFromMessage($message);

        if ($this->eventDispatcher->hasListeners(MessagesEvents::STATUS_MESSAGE_EVENT)) {
            /** @var StatusMessageEvent $event */
            $this->eventDispatcher->dispatch(
                MessagesEvents::STATUS_MESSAGE_EVENT,
                new StatusMessageEvent($statusMessage)
            );
        }
    }


    /**
     * @param Message $message
     * @param string  $status
     * @param string  $statusMessage
     */
    protected function dispatchSetMessageStatusEvent(Message $message, string $status, string $statusMessage)
    {
        if ($this->eventDispatcher->hasListeners(MessagesEvents::SET_MESSAGE_STATUS)) {
            /** @var ReadMessageEvent $event */
            $this->eventDispatcher->dispatch(
                MessagesEvents::SET_MESSAGE_STATUS,
                new SetMessageStatusEvent($message, $status, $statusMessage)
            );
        }
    }


    /**
     * Set event as processed and stop propagation of the event
     *
     * @param ReadMessageEvent $event
     */
    protected function setEventAsProcessed(ReadMessageEvent $event)
    {
        $event->stopPropagation();
        //This call is important!
        //The class which dispatched the event will check whether any listener handled the message or not.
        $event->setEventProcessed(true);
    }
}
