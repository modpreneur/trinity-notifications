<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54.
 */
namespace Trinity\NotificationBundle\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Entity\NotificationStatusMessage;
use Trinity\NotificationBundle\Event\AfterNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\BeforeNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;

/**
 * Class NotificationReader.
 */
class NotificationReader
{
    /** @var NotificationParser */
    protected $parser;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  MessageSender */
    protected $messageSender;

    /** @var  NotificationLoggerInterface */
    protected $notificationLogger;

    /**
     * NotificationReader constructor.
     *
     * @param NotificationParser          $parser
     * @param EventDispatcherInterface    $eventDispatcher
     * @param MessageSender               $messageSender
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function __construct(
        NotificationParser $parser,
        EventDispatcherInterface $eventDispatcher,
        MessageSender $messageSender,
        NotificationLoggerInterface $notificationLogger
    ) {
        $this->parser = $parser;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageSender = $messageSender;
        $this->notificationLogger = $notificationLogger;
    }

    /**
     * Handle notification message.
     * This method will be probably refactored to standalone class.
     * This is the entry method for integration testing.
     *
     * @param Message $message
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Exception
     */
    public function read(Message $message) : array
    {
        $notificationBatch = NotificationBatch::createFromMessage($message);
        $notifications = $notificationBatch->getNotifications()->toArray();

        $this->eventDispatcher->dispatch(
            BeforeNotificationBatchProcessEvent::NAME,
            new BeforeNotificationBatchProcessEvent($notificationBatch->getUser(), $notificationBatch->getClientId())
        );

        $this->logNotifications($notifications);

        try {
            $entities = $this->parser->parseNotifications(
                $notifications
            );

            $this->logNotificationsSuccess($notificationBatch);
            $this->sendStatusMessage($notificationBatch);

            $this->dispatchChangesDoneEvent($entities, $notificationBatch);
            $this->dispatchEndEvent($notificationBatch);
        } catch (AssociationEntityNotFoundException $e) {
            $e->setMessageObject($notificationBatch);
            $this->logNotificationsError($notificationBatch, $e);

            $this->dispatchAssocEntityNotFound($e);
            $this->dispatchEndEvent($notificationBatch, $e);

            throw $e;
        } catch (\Exception $e) {
            $this->dispatchEndEvent($notificationBatch, $e);
            $this->logNotificationsError($notificationBatch, $e);

            throw $e;
        }

        return $entities;
    }

    /**
     * @param NotificationEntityInterface[] $entities
     * @param NotificationBatch             $notificationBatch
     */
    protected function dispatchChangesDoneEvent(array $entities, NotificationBatch $notificationBatch)
    {
        if ($this->eventDispatcher->hasListeners(ChangesDoneEvent::NAME)) {
            $event = new ChangesDoneEvent($entities, $notificationBatch);
            $this->eventDispatcher->dispatch(ChangesDoneEvent::NAME, $event);
        }
    }

    /**
     * @param AssociationEntityNotFoundException $exception
     */
    protected function dispatchAssocEntityNotFound(AssociationEntityNotFoundException $exception)
    {
        if ($this->eventDispatcher->hasListeners(AssociationEntityNotFoundEvent::NAME)) {
            $event = new AssociationEntityNotFoundEvent($exception);
            $this->eventDispatcher->dispatch(AssociationEntityNotFoundEvent::NAME, $event);
        }
    }

    /**
     * @param NotificationBatch $batch
     *
     * @throws \InvalidArgumentException
     */
    protected function logNotificationsSuccess(NotificationBatch $batch)
    {
        $this->logNotificationsStatus($batch, NotificationStatus::STATUS_OK, 'ok');
    }

    /**
     * @param NotificationBatch $batch
     * @param \Exception        $exception
     *
     * @throws \InvalidArgumentException
     */
    protected function logNotificationsError(NotificationBatch $batch, \Exception $exception)
    {
        //todo: log as errored only actually errored notifications?
        $this->logNotificationsStatus($batch, NotificationStatus::STATUS_ERROR, $exception->getMessage());
    }

    /**
     * @param NotificationBatch $batch
     * @param string            $status
     * @param string            $message
     *
     * @throws \InvalidArgumentException
     */
    protected function logNotificationsStatus(NotificationBatch $batch, string $status, string $message)
    {
        $statuses = [];

        /** @var Notification $notification */
        foreach ($batch->getNotifications() as $notification) {
            $notificationStatus = new NotificationStatus();
            $notificationStatus->setStatus($status);
            $notificationStatus->setMessage($message);
            $notificationStatus->setNotificationId($notification->getUid());
            $statuses[] = $notificationStatus;
        }

        $this->notificationLogger->setNotificationStatuses($statuses);
    }

    /**
     * @param array $notifications
     */
    protected function logNotifications(array $notifications)
    {
        foreach ($notifications as $notification) {
            $this->notificationLogger->logIncomingNotification($notification);
        }
    }

    /**
     * @param NotificationBatch $batch
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \InvalidArgumentException
     */
    protected function sendStatusMessage(NotificationBatch $batch)
    {
        $notifications = $batch->getNotifications();

        $statusMessage = new NotificationStatusMessage();
        $statusMessage->setParentMessageUid($batch->getUid());
        $statusMessage->setClientId($batch->getClientId());
        $statusMessage->setDestination($batch->getSender());

        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            if (($exception = $this->getExceptionForNotification($notification)) !== null) {
                $notificationStatus = new NotificationStatus();
                $notificationStatus->setStatus(NotificationStatus::STATUS_ERROR);
                $notificationStatus->setMessage($exception->getMessage());
                $notificationStatus->setNotificationId($notification->getUid());
                $notificationStatus->setExtra(['violations' => $exception->getViolations()]);
                $statusMessage->addNotificationStatus($notificationStatus);
            } else {
                $notificationStatus = new NotificationStatus();
                $notificationStatus->setStatus(NotificationStatus::STATUS_OK);
                $notificationStatus->setMessage('ok');
                $notificationStatus->setNotificationId($notification->getUid());
                $statusMessage->addNotificationStatus($notificationStatus);
            }
        }

        $this->messageSender->sendMessage($statusMessage);
    }

    /**
     * @param Notification $notification
     *
     * @return null|\Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     */
    public function getExceptionForNotification(Notification $notification)
    {
        foreach ($this->parser->getNotificationExceptions() as $exception) {
            if ($exception->getNotification() === $notification) {
                return $exception;
            }
        }

        return;
    }

    /**
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function setNotificationLogger(NotificationLoggerInterface $notificationLogger)
    {
        $this->notificationLogger = $notificationLogger;
    }

    /**
     * @param NotificationBatch $batch
     * @param \Exception|null   $exception
     */
    protected function dispatchEndEvent(NotificationBatch $batch, \Exception $exception = null)
    {
        $this->eventDispatcher->dispatch(
            AfterNotificationBatchProcessEvent::NAME,
            new AfterNotificationBatchProcessEvent(
                $batch->getUser(),
                $batch->getClientId(),
                $exception
            )
        );
    }
}
