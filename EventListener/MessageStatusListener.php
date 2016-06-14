<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.05.16
 * Time: 9:00
 */

namespace Trinity\NotificationBundle\EventListener;

use Trinity\Bundle\MessagesBundle\Event\SetMessageStatusEvent;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;

/**
 * Class MessageStatusListener
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class MessageStatusListener
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
}
