<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 14.04.16
 * Time: 8:44.
 */
namespace Trinity\NotificationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Trinity\Bundle\MessagesBundle\Event\ReadMessageEvent;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationRequestMessage;
use Trinity\NotificationBundle\Entity\NotificationStatusMessage;
use Trinity\NotificationBundle\Entity\StopSynchronizationForClientEvent;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;
use Trinity\NotificationBundle\Notification\NotificationEventDispatcher;
use Trinity\NotificationBundle\Notification\NotificationManager;
use Trinity\NotificationBundle\Notification\NotificationReader;

/**
 * Class NotificationEventsListener.
 */
class NotificationEventsListener implements EventSubscriberInterface
{
    /** @var  NotificationReader */
    protected $notificationReader;

    /** @var  NotificationEventDispatcher */
    protected $eventDispatcher;

    /** @var  NotificationManager */
    protected $notificationManager;

    /** @var  NotificationLoggerInterface */
    protected $notificationLogger;

    /** @var  bool */
    protected $isClient;

    /**
     * NotificationEventsListener constructor.
     *
     * @param NotificationReader       $notificationReader
     * @param NotificationEventDispatcher $eventDispatcher
     * @param NotificationManager      $notificationManager
     * @param bool                     $isClient
     */
    public function __construct(
        NotificationReader $notificationReader,
        NotificationEventDispatcher $eventDispatcher,
        NotificationManager $notificationManager,
        bool $isClient
    ) {
        $this->notificationReader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationManager = $notificationManager;
        $this->isClient = $isClient;
    }

    /**
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function setNotificationLogger(NotificationLoggerInterface $notificationLogger)
    {
        $this->notificationLogger = $notificationLogger;
    }

    /**
     * @param ReadMessageEvent $event
     *
     * @throws \Exception
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
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
        } elseif ($message->getType() === NotificationStatusMessage::MESSAGE_TYPE) {
            $this->handleNotificationStatusMessage($message);
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
            $event->getChangeSet(),
            $event->getForced(),
            $event->getMethod(),
            !$this->isClient,
            $event->getOptions()
        );
    }

    public function onStopSynchronizationForClientEvent(StopSynchronizationForClientEvent $event)
    {
        $this->notificationManager->disableNotification($event->getClient());
    }

    /**
     * @param Message $message
     */
    protected function handleNotificationStatusMessage(Message $message)
    {
        $message = NotificationStatusMessage::createFromMessage($message);
        $statuses = $message->getAllStatuses()->toArray();
        $this->notificationLogger->setNotificationStatuses($statuses);
    }

    /**
     * @param Message $message
     *
     * @return array
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Exception
     * @throws \Throwable  Catches all catchable errors and exceptions and then throws them again
     */
    protected function handleNotificationMessage(Message $message)
    {
        try {
            $message = NotificationBatch::createFromMessage($message);
            $entities = $this->notificationReader->read($message);
            $this->eventDispatcher->dispatchSetMessageStatusEvent($message, StatusMessage::STATUS_OK, 'ok');
            return $entities;
        } catch (\Throwable $error) {
            $this->eventDispatcher->dispatchSetMessageStatusEvent(
                $message,
                StatusMessage::STATUS_ERROR,
                (string) $error
            );

            throw $error;
        }
    }

    /**
     * @param Message $message
     */
    protected function handleNotificationRequest(Message $message)
    {
        $this->eventDispatcher->dispatchNotificationRequestEvent($message);
    }

    /**
     * @param Message $message
     */
    protected function handleStatusMessage(Message $message)
    {
        $statusMessage = StatusMessage::createFromMessage($message);
        $this->eventDispatcher->dispatchStatusMessageEvent($statusMessage);
    }

    /**
     * @param Message $message
     * @param string  $status
     * @param string  $statusMessage
     */
    protected function dispatchSetMessageStatusEvent(Message $message, string $status, string $statusMessage)
    {
        $this->eventDispatcher->dispatchSetMessageStatusEvent($message, $status, $statusMessage);
    }

    /**
     * Set event as processed and stop propagation of the event.
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

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ReadMessageEvent::NAME => ['onMessageRead', 100],
            SendNotificationEvent::NAME => ['onSendNotificationEvent', 100],
            StopSynchronizationForClientEvent::NAME => ['onStopSynchronizationForClientEvent', 100],
        ];
    }
}
