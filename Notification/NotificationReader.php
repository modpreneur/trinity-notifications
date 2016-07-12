<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54
 */

namespace Trinity\NotificationBundle\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Event\AfterNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\BeforeNotificationBatchProcessEvent;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class NotificationReader
 *
 * @package Trinity\NotificationBundle\Notification
 */
class NotificationReader
{
    /** @var NotificationParser */
    protected $parser;


    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;


    /**
     * NotificationReader constructor.
     *
     * @param NotificationParser       $parser
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NotificationParser $parser,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->parser = $parser;
        $this->eventDispatcher = $eventDispatcher;
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
     *
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
                $notificationBatch->getNotifications()->toArray(),
                (new \DateTime('now'))->setTimestamp($notificationBatch->getCreatedAt())
            );

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
