<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 14.04.16
 * Time: 8:44
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\BunnyBundle\Event\RabbitMessageConsumedEvent;
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationRequestMessage;
use Trinity\NotificationBundle\Entity\StatusMessage;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\MessageReadEvent;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Event\SetMessageStatusEvent;
use Trinity\NotificationBundle\Event\StatusMessageEvent;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;
use Trinity\NotificationBundle\Message\MessageReader;
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

    /** @var ClientSecretProviderInterface */
    protected $clientSecretProvider;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  MessageReader */
    protected $messageReader;


    /**
     * NotificationEventsListener constructor.
     *
     * @param NotificationReader       $notificationReader
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface   $entityManager
     * @param MessageReader            $messageReader
     */
    public function __construct(
        NotificationReader $notificationReader,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        MessageReader $messageReader
    ) {
        $this->notificationReader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->messageReader = $messageReader;
    }


    /**
     * @param MessageReadEvent $event
     *
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Exception
     */
    public function onMessageRead(MessageReadEvent $event)
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
     * @param RabbitMessageConsumedEvent $event
     */
    public function onRabbitMessageConsumed(RabbitMessageConsumedEvent $event)
    {
        $this->messageReader->read($event->getMessage(), $event->getSourceQueue());
    }


    /**
     * @param Message $message
     *
     * @return array
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
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
        if ($this->eventDispatcher->hasListeners(Events::NOTIFICATION_REQUEST_EVENT)) {
            /** @var NotificationRequestEvent $event */
            $this->eventDispatcher->dispatch(Events::NOTIFICATION_REQUEST_EVENT, $event);
        }
    }


    /**
     * @param Message $message
     */
    protected function handleStatusMessage(Message $message)
    {
        $statusMessage = StatusMessage::createFromMessage($message);

        if ($this->eventDispatcher->hasListeners(Events::STATUS_MESSAGE_EVENT)) {
            /** @var StatusMessageEvent $event */
            $this->eventDispatcher->dispatch(Events::STATUS_MESSAGE_EVENT, new StatusMessageEvent($statusMessage));
        }
    }


    /**
     * @param Message $message
     * @param string  $status
     * @param string  $statusMessage
     */
    protected function dispatchSetMessageStatusEvent(Message $message, string $status, string $statusMessage)
    {
        if ($this->eventDispatcher->hasListeners(Events::SET_MESSAGE_STATUS)) {
            /** @var MessageReadEvent $event */
            $this->eventDispatcher->dispatch(
                Events::SET_MESSAGE_STATUS,
                new SetMessageStatusEvent($message, $status, $statusMessage)
            );
        }
    }


    /**
     * Set event as processed and stop propagation of the event
     *
     * @param MessageReadEvent $event
     */
    protected function setEventAsProcessed(MessageReadEvent $event)
    {
        $event->stopPropagation();
        //This call is important!
        //The class which dispatched the event will check whether any listener handled the message or not.
        $event->setEventProcessed(true);
    }
}