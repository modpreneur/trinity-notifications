<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54.
 */
namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Entity\NotificationStatusMessage;
use Trinity\NotificationBundle\Event\AfterNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\BeforeNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

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

    /**
     * NotificationReader constructor.
     *
     * @param NotificationParser       $parser
     * @param EventDispatcherInterface $eventDispatcher
     * @param MessageSender            $messageSender
     */
    public function __construct(
        NotificationParser $parser,
        EventDispatcherInterface $eventDispatcher,
        MessageSender $messageSender
    ) {
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

        $this->eventDispatcher->dispatch(
            BeforeNotificationBatchProcessEvent::NAME,
            new BeforeNotificationBatchProcessEvent($notificationBatch->getUser(), $notificationBatch->getClientId())
        );

        try {
            $entities = $this->parser->parseNotifications(
                $notificationBatch->getNotifications()->toArray()
            );

            $this->sendStatusMessage($notificationBatch);

            $this->dispatchEndEvent($notificationBatch);
        } catch (AssociationEntityNotFoundException $e) {
            $e->setMessageObject($notificationBatch);

            if ($this->eventDispatcher->hasListeners(AssociationEntityNotFoundEvent::NAME)) {
                $event = new AssociationEntityNotFoundEvent($e);
                $this->eventDispatcher->dispatch(AssociationEntityNotFoundEvent::NAME, $event);
            }

            $this->dispatchEndEvent($notificationBatch, $e);

            throw $e;
        } catch (\Exception $e) {
            $this->dispatchEndEvent($notificationBatch, $e);

            throw $e;
        }

        if ($this->eventDispatcher->hasListeners(ChangesDoneEvent::NAME)) {
            $event = new ChangesDoneEvent($entities, $notificationBatch);
            $this->eventDispatcher->dispatch(ChangesDoneEvent::NAME, $event);
        }

        return $entities;
    }

    /**
     * @param NotificationBatch $batch
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
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

        return null;
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
