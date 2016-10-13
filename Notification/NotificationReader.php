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
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Entity\NotificationStatusMessage;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;

/**
 * Class NotificationReader.
 */
class NotificationReader
{
    /** @var NotificationParser */
    protected $parser;

    /** @var  NotificationEventDispatcher */
    protected $eventDispatcher;

    /** @var  MessageSender */
    protected $messageSender;

    /** @var  NotificationLoggerInterface */
    protected $notificationLogger;

    /** @var  NotificationConstraints */
    protected $constraints;

    /** @var  EntityAssociator */
    protected $entityAssociator;

    /**
     * NotificationReader constructor.
     *
     * @param NotificationParser $parser
     * @param NotificationEventDispatcher $eventDispatcher
     * @param MessageSender $messageSender
     */
    public function __construct(
        NotificationParser $parser,
        NotificationEventDispatcher $eventDispatcher,
        MessageSender $messageSender
    )
    {
        $this->parser = $parser;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageSender = $messageSender;
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
        $entities = [];

        $this->eventDispatcher->dispatchBeforeNotificationBatchProcessEvent($notificationBatch);
        $this->logNotifications($notifications);

        try {
            $entities = $this->parser->parseNotifications(
                $notifications
            );

            $this->entityAssociator->associate($entities);

            $this->successfullyRead($notificationBatch, $entities);
        } catch (AssociationEntityNotFoundException $e) {
            $this->handleAssociationEntityNotFoundException($e, $notificationBatch);
        } catch (\Exception $e) {
            $this->handleGenericException($e, $notificationBatch);
        }

        return $entities;
    }

    /**
     * @param NotificationBatch $notificationBatch
     * @param array $entities
     *
     * @throws \InvalidArgumentException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     */
    protected function successfullyRead(NotificationBatch $notificationBatch, array $entities)
    {
        $this->logNotificationsSuccess($notificationBatch);
        $this->sendStatusMessage($notificationBatch);
        $this->eventDispatcher->dispatchChangesDoneEvent($entities, $notificationBatch);
        $this->eventDispatcher->dispatchAfterNotificationBatchProcessEvent($notificationBatch);
    }

    /**
     * @param \Exception $exception
     * @param NotificationBatch $notificationBatch
     *
     * @throws \Exception
     */
    protected function handleGenericException(\Exception $exception, NotificationBatch $notificationBatch)
    {
        $this->eventDispatcher->dispatchAfterNotificationBatchProcessEvent($notificationBatch, $exception);
        $this->logNotificationsError($notificationBatch, $exception);

        throw $exception;
    }

    /**
     * @param AssociationEntityNotFoundException $exception
     * @param NotificationBatch $notificationBatch
     *
     * @throws AssociationEntityNotFoundException
     * @throws \InvalidArgumentException
     */
    protected function handleAssociationEntityNotFoundException(
        AssociationEntityNotFoundException $exception,
        NotificationBatch $notificationBatch
    ) {
        $exception->setMessageObject($notificationBatch);
        $this->logNotificationsError($notificationBatch, $exception);

        $this->eventDispatcher->dispatchAssocEntityNotFound($exception);
        $this->eventDispatcher->dispatchAfterNotificationBatchProcessEvent($notificationBatch, $exception);

        throw $exception;
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
     * @param \Exception $exception
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
     * @param string $status
     * @param string $message
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
    }

    /**
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function setNotificationLogger(NotificationLoggerInterface $notificationLogger)
    {
        $this->notificationLogger = $notificationLogger;
    }
}