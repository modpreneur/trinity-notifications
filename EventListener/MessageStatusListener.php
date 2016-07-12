<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.05.16
 * Time: 9:00
 */

namespace Trinity\NotificationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Trinity\Bundle\MessagesBundle\Event\SetMessageStatusEvent;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;

/**
 * Class MessageStatusListener
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class MessageStatusListener implements EventSubscriberInterface
{
    /** @var  MessageSender */
    protected $messageSender;

    /** @var  bool */
    protected $isClient;


    /**
     * MessageStatusListener constructor.
     *
     * @param MessageSender $messageSender
     * @param bool          $isClient
     */
    public function __construct(
        MessageSender $messageSender,
        bool $isClient
    ) {
        $this->messageSender = $messageSender;
        $this->isClient = $isClient;
    }


    /**
     * @param SetMessageStatusEvent $event
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\InvalidMessageStatusException
     */
    public function onSetMessageStatus(SetMessageStatusEvent $event)
    {
        if ($this->isClient) {
            //create confirmation message
            $message = new StatusMessage();
            $message->setStatus($event->getStatus());
            $message->setStatusMessage($event->getStatusMessage());
            $message->setParentMessageUid($event->getMessage()->getUid());
            $message->setClientId($event->getMessage()->getClientId());
            $message->setDestination('server');

            //send it
            $this->messageSender->sendMessage($message);
        }
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
            SetMessageStatusEvent::NAME => 'onSetMessageStatus'
        ];
    }
}
