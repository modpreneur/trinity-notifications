<?php

namespace Trinity\NotificationBundle\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\MessagesBundle\Event\SetMessageStatusEvent;
use Trinity\Bundle\MessagesBundle\Event\StatusMessageEvent;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\StopSynchronizationForClientEvent;
use Trinity\NotificationBundle\Event\AfterDriverExecuteEvent;
use Trinity\NotificationBundle\Event\AfterNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\BeforeDeleteEntityEvent;
use Trinity\NotificationBundle\Event\BeforeDriverExecuteEvent;
use Trinity\NotificationBundle\Event\BeforeNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\BeforeParseNotificationEvent;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class NotificationEventDispatcher
 */
class NotificationEventDispatcher
{
    //todo @JakubFajkus remove all hasListeners...
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * NotificationEventDispatcher constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatchBeforeNotificationBatchProcessEvent(NotificationBatch $notificationBatch)
    {
        $this->eventDispatcher->dispatch(
            BeforeNotificationBatchProcessEvent::NAME,
            new BeforeNotificationBatchProcessEvent($notificationBatch->getUser(), $notificationBatch->getClientId())
        );
    }

    /**
     * @param NotificationEntityInterface[] $entities
     * @param NotificationBatch             $notificationBatch
     */
    public function dispatchChangesDoneEvent(array $entities, NotificationBatch $notificationBatch)
    {
        if ($this->eventDispatcher->hasListeners(ChangesDoneEvent::NAME)) {
            $event = new ChangesDoneEvent($entities, $notificationBatch);
            $this->eventDispatcher->dispatch(ChangesDoneEvent::NAME, $event);
        }
    }

    /**
     * @param AssociationEntityNotFoundException $exception
     */
    public function dispatchAssocEntityNotFound(AssociationEntityNotFoundException $exception)
    {
        if ($this->eventDispatcher->hasListeners(AssociationEntityNotFoundEvent::NAME)) {
            $event = new AssociationEntityNotFoundEvent($exception);
            $this->eventDispatcher->dispatch(AssociationEntityNotFoundEvent::NAME, $event);
        }
    }

    /**
     * @param NotificationBatch $batch
     * @param \Exception|null   $exception
     */
    public function dispatchAfterNotificationBatchProcessEvent(
        NotificationBatch $batch,
        \Exception $exception = null
    ) {
        $this->eventDispatcher->dispatch(
            AfterNotificationBatchProcessEvent::NAME,
            new AfterNotificationBatchProcessEvent(
                $batch->getUser(),
                $batch->getClientId(),
                $exception
            )
        );
    }

    /**
     * @param NotificationEntityInterface|null $entity
     */
    public function dispatchBeforeDeleteEntityEvent(NotificationEntityInterface $entity = null)
    {
        $this->eventDispatcher->dispatch(
            BeforeDeleteEntityEvent::NAME,
            new BeforeDeleteEntityEvent($entity)
        );
    }

    /**
     * @param Notification $notification
     * @param string       $fullClassName
     */
    public function dispatchBeforeParseNotificationEvent(Notification $notification, string $fullClassName)
    {
        // If there are listeners for this event,
        // fire it and get the message from it(it allows changing the data, className and method)
        if ($this->eventDispatcher->hasListeners(BeforeParseNotificationEvent::NAME)) {
            $event = new BeforeParseNotificationEvent($notification, $fullClassName);
            $this->eventDispatcher->dispatch(
                BeforeParseNotificationEvent::NAME,
                $event
            );
        }
    }

    /**
     * @param NotificationEntityInterface $entity
     */
    public function dispatchBeforeDriverExecuteEvent(NotificationEntityInterface $entity)
    {
        $beforeDriverExecute = new BeforeDriverExecuteEvent($entity);
        /** @var BeforeDriverExecuteEvent $beforeDriverExecute */

        $this->eventDispatcher->dispatch(
            BeforeDriverExecuteEvent::NAME,
            $beforeDriverExecute
        );
    }

    /**
     * @param NotificationEntityInterface $entity
     */
    public function dispatchAfterDriverExecuteEvent(NotificationEntityInterface $entity)
    {
        $afterDriverExecute = new AfterDriverExecuteEvent($entity);
        /* @var AfterDriverExecuteEvent $afterDriverExecute */
        $this->eventDispatcher->dispatch(AfterDriverExecuteEvent::NAME, $afterDriverExecute);
    }

    /**
     * @param NotificationEntityInterface|null $entity
     * @param array $changeSet
     * @param string $method
     * @param array $options
     * @param bool $force
     */
    public function dispatchSendNotificationEvent(
        NotificationEntityInterface $entity = null,
        array $changeSet,
        string $method,
        array $options,
        bool $force = false
    ) {
        $this->eventDispatcher->dispatch(
            SendNotificationEvent::NAME,
            new SendNotificationEvent($entity, $changeSet, $method, $options, $force)
        );
    }

    /**
     * @param Message $message
     */
    public function dispatchNotificationRequestEvent(Message $message)
    {
        $this->eventDispatcher->dispatch(NotificationRequestEvent::NAME, new NotificationRequestEvent($message));
    }

    /**
     * @param StatusMessage $statusMessage
     */
    public function dispatchStatusMessageEvent(StatusMessage $statusMessage)
    {
        $this->eventDispatcher->dispatch(StatusMessageEvent::NAME, new StatusMessageEvent($statusMessage));
    }

    /**
     * @param Message $message
     * @param string $status
     * @param string $statusMessage
     */
    public function dispatchSetMessageStatusEvent(Message $message, string $status, string $statusMessage)
    {
        $this->eventDispatcher->dispatch(
            SetMessageStatusEvent::NAME,
            new SetMessageStatusEvent($message, $status, $statusMessage)
        );
    }

    /**
     * @param ClientInterface $client
     */
    public function dispatchStopSynchronizationForClientEvent(ClientInterface $client = null)
    {
        $event = new StopSynchronizationForClientEvent($client);
        $this->eventDispatcher->dispatch(StopSynchronizationForClientEvent::NAME, $event);
    }
}