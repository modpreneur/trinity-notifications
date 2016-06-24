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
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Event\Events;
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
     * @throws \Trinity\NotificationBundle\Exception\EntityWasUpdatedBeforeException
     *
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

        try {
            $entities = $this->parser->parseNotifications(
                $notificationBatch->getNotifications()->toArray(),
                $notificationBatch->getCreatedOn()
            );
        } catch (AssociationEntityNotFoundException $e) {
            $e->setMessageObject($notificationBatch);

            if ($this->eventDispatcher->hasListeners(Events::ASSOCIATION_ENTITY_NOT_FOUND)) {
                $event = new AssociationEntityNotFoundEvent($e);
                $this->eventDispatcher->dispatch(Events::ASSOCIATION_ENTITY_NOT_FOUND, $event);
            }

            throw $e;
        }

        if ($this->eventDispatcher->hasListeners(Events::CHANGES_DONE_EVENT)) {
            $event = new ChangesDoneEvent($entities, $notificationBatch);
            $this->eventDispatcher->dispatch(Events::CHANGES_DONE_EVENT, $event);
        }

        return $entities;
    }
}
