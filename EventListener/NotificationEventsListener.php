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
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Event\ChangesDoneEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\MessageReadEvent;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;
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
    const NOTIFICATION_MESSAGE_TYPE = 'notification';
    const NOTIFICATION_REQUEST_MESSAGE_TYPE = 'notificationRequest';

    /** @var  NotificationReader */
    protected $notificationReader;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ClientSecretProviderInterface */
    protected $clientSecretProvider;

    /** @var  EntityManagerInterface */
    protected $entityManager;


    /**
     * NotificationEventsListener constructor.
     *
     * @param NotificationReader $notificationReader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NotificationReader $notificationReader,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->notificationReader = $notificationReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }


    /**
     * @param MessageReadEvent $event
     *
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     */
    public function onMessageRead(MessageReadEvent $event)
    {
        $message = $event->getMessage();

        /*
         * If the message type was recognized handle it.
         * If not just let it be because another listener may recognize it
         */
        if ($message->getType() === self::NOTIFICATION_MESSAGE_TYPE) {
            $this->handleNotificationMessage($message);
            $this->setEventAsProcessed($event);

        } elseif ($message->getType() === self::NOTIFICATION_REQUEST_MESSAGE_TYPE) {
            $this->handleNotificationRequest($message);
            $this->setEventAsProcessed($event);
        }
    }


    /**
     * @param ChangesDoneEvent $event
     */
    public function onChangesDone(ChangesDoneEvent $event)
    {
        $entities = $event->getEntities();

        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        
        $this->entityManager->flush();
    }


    /**
     * @param Message $message
     *
     * @return array
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Exception
     *
     */
    protected function handleNotificationMessage(Message $message)
    {
        return $this->notificationReader->read($message);
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