<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 16:22
 */

namespace Trinity\NotificationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Notification\NotificationRequestHandler;

/**
 * Class NotificationRequestListener
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class NotificationRequestListener implements EventSubscriberInterface
{
    /** @var  NotificationRequestHandler */
    protected $requestHandler;


    /**
     * NotificationRequestListener constructor.
     *
     * @param NotificationRequestHandler $requestHandler
     */
    public function __construct(NotificationRequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }


    /**
     * Listen to a AssociationEntityNotFoundEvent. When the event is fired, publish a message with
     * notification request. The request will contain the original message uid.
     *
     * @param AssociationEntityNotFoundEvent $event
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function onAssociationEntityNotFoundEvent(AssociationEntityNotFoundEvent $event)
    {
        $this->requestHandler->handleMissingEntityException($event->getException());
    }


    /**
     * Listen to a NotificationRequestEvent. When the event is fired, handle it.
     * This means understanding the request and performing actions.
     * E.g. if the association entity was not found, send a request to get it.
     *
     * @param NotificationRequestEvent $event
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     */
    public function onNotificationRequestEvent(NotificationRequestEvent $event)
    {
        $this->requestHandler->handleMissingEntityRequestMessage($event->getMessage());
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
            AssociationEntityNotFoundEvent::NAME => 'onAssociationEntityNotFoundEvent',
            NotificationRequestEvent::NAME => 'onNotificationRequestEvent'
        ];
    }
}
