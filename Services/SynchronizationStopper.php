<?php

namespace Trinity\NotificationBundle\Services;

use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\SynchronizationStoppedMessage;

/**
 * Class SynchronizationStopper.
 */
class SynchronizationStopper
{
    /** @var  MessageSender */
    protected $messageSender;

    /** @var  EntityAliasTranslator */
    protected $entityAliasTranslator;

    /**
     * SynchronizationStopper constructor.
     *
     * @param MessageSender         $messageSender
     * @param EntityAliasTranslator $entityAliasTranslator
     */
    public function __construct(MessageSender $messageSender, EntityAliasTranslator $entityAliasTranslator)
    {
        $this->messageSender = $messageSender;
        $this->entityAliasTranslator = $entityAliasTranslator;
    }

    /**
     * Send SynchronizationStoppedMessage to the given client.
     *
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     */
    public function sendStopSynchronizationMessage(NotificationEntityInterface $entity, ClientInterface $client)
    {
        $message = new SynchronizationStoppedMessage();
        $message->setEntityAlias($this->entityAliasTranslator->getAliasFromClass(get_class($entity)));
        $message->setEntityId($entity->getId());
        $message->setClientId($client->getId());
        $message->setDestination('client_'.$message->getClientId());

        $this->messageSender->sendMessage($message);
    }
}
