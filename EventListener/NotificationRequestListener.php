<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 16:22
 */

namespace Trinity\NotificationBundle\EventListener;

use Trinity\NotificationBundle\Event\AssociationEntityNotFoundExceptionThrown;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Notification\NotificationRequestHandler;

/**
 * Class NotificationRequestListener
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class NotificationRequestListener
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
     * Listen to a AssociationEntityNotFoundExceptionThrown. When the event is fired, publish a message with
     * notification request. The request will contain the original message uid.
     *
     * @param AssociationEntityNotFoundExceptionThrown $event
     */
    public function onAssociationEntityNotFoundExceptionThrown(AssociationEntityNotFoundExceptionThrown $event)
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
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     */
    public function onNotificationRequestEvent(NotificationRequestEvent $event)
    {
        $this->requestHandler->handleMissingEntityRequestEvent($event);
    }
}