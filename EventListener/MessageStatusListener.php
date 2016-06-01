<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.05.16
 * Time: 9:00
 */

namespace Trinity\NotificationBundle\EventListener;

use Trinity\NotificationBundle\Entity\StatusMessage;
use Trinity\NotificationBundle\Event\SetMessageStatusEvent;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;
use Trinity\NotificationBundle\Message\MessageManager;

/**
 * Class MessageStatusListener
 *
 * @package Trinity\NotificationBundle\EventListener
 */
class MessageStatusListener
{
    /** @var  MessageManager */
    protected $messageManager;

    /** @var  ClientSecretProviderInterface */
    protected $clientSecretProvider;

    /** @var  bool */
    protected $isClient;


    /**
     * MessageStatusListener constructor.
     *
     * @param MessageManager $messageManager
     * @param bool           $isClient
     */
    public function __construct(
        MessageManager $messageManager,
        bool $isClient
    ) {
        $this->messageManager = $messageManager;
        $this->isClient = $isClient;
    }


    /**
     * @param SetMessageStatusEvent $event
     *
     * @throws \Trinity\NotificationBundle\Exception\InvalidMessageStatusException
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     */
    public function onSetMessageStatus(SetMessageStatusEvent $event)
    {
        if ($this->isClient) {
            //create confirmation message
            $message = new StatusMessage();
            $message->setStatus($event->getStatus());
            $message->setParentMessageUid($event->getMessage()->getUid());
            $message->setClientId($event->getMessage()->getClientId());
            $message->setClientSecret(
                $this->clientSecretProvider->getClientSecret(
                    $event->getMessage()->getClientId()
                )
            );

            //send it
            $this->messageManager->sendMessage($message, 'message');
        }
    }


    /**
     * @param ClientSecretProviderInterface $clientSecretProvider
     */
    public function setClientSecretProvider(ClientSecretProviderInterface $clientSecretProvider)
    {
        $this->clientSecretProvider = $clientSecretProvider;
    }
}
