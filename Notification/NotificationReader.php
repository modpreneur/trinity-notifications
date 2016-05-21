<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundExceptionThrown;
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
    /**
     * @var NotificationParser
     */
    protected $parser;


    /**
     * @var  EventDispatcherInterface
     */
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
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     *
     * @throws \Exception
     */
    public function read(Message $message)
    {
        /** @var array $notificationsArrays */
        $notificationsArrays = $message->getRawData();
        $notifications = [];

        //convert all notifications to objects
        foreach ($notificationsArrays as $notificationArray) {
            $notifications[] = Notification::fromArray($notificationArray);
        }

        try {
            $entities = $this->parser->parseNotifications($notifications);
        } catch (AssociationEntityNotFoundException $e) {
            $e->setMessageId($message->getUid());

            if ($this->eventDispatcher->hasListeners(Events::ASSOCIATION_ENTITY_NOT_FOUND_EXCEPTION_THROWN)) {
                $event = new AssociationEntityNotFoundExceptionThrown($e);
                $this->eventDispatcher->dispatch(Events::ASSOCIATION_ENTITY_NOT_FOUND_EXCEPTION_THROWN, $event);
            }

            throw $e;
        }

        if ($this->eventDispatcher->hasListeners(Events::CHANGES_DONE_EVENT)) {
            $event = new ChangesDoneEvent($entities);
            $this->eventDispatcher->dispatch(Events::CHANGES_DONE_EVENT, $event);
        }

        return $entities;
    }
}