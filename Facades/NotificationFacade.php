<?php

namespace Trinity\NotificationBundle\Facades;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\StopSynchronizationForClientEvent;
use Trinity\NotificationBundle\Exception\EntityAliasNotFoundException;
use Trinity\NotificationBundle\Notification\NotificationManager;
use Trinity\NotificationBundle\Services\EntityAliasTranslator;
use Trinity\NotificationBundle\Services\SynchronizationStopper;

/**
 * Class NotificationFacade.
 */
class NotificationFacade
{
    /** @var SynchronizationStopper */
    protected $synchronizationStopper;

    /** @var  NotificationManager */
    protected $notificationManager;

    /** @var  EntityAliasTranslator */
    protected $aliasTranslator;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * NotificationFacade constructor.
     *
     * @param SynchronizationStopper   $synchronizationStopper
     * @param NotificationManager      $notificationManager
     * @param EntityAliasTranslator    $aliasTranslator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SynchronizationStopper $synchronizationStopper,
        NotificationManager $notificationManager,
        EntityAliasTranslator $aliasTranslator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->synchronizationStopper = $synchronizationStopper;
        $this->notificationManager = $notificationManager;
        $this->aliasTranslator = $aliasTranslator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Stop synchronization of the entity with the client.
     *
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     */
    public function stopSynchronization(NotificationEntityInterface $entity, ClientInterface $client)
    {
        $this->synchronizationStopper->sendStopSynchronizationMessage($entity, $client);
    }

    /**
     * Synchronize the entity with the client.
     *
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param bool                        $force  Should be the data of the sender overridden in case of unexpected state?
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     */
    public function synchronizeEntity(NotificationEntityInterface $entity, ClientInterface $client, $force)
    {
        $this->notificationManager->syncEntity($entity, $client, $force);
    }

    /**
     * Synchronize the entities with the client.
     *
     * @param $entities
     * @param $client
     * @param $forced
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     */
    public function synchronizeEntities(array $entities, ClientInterface $client, $forced)
    {
        $this->notificationManager->syncEntities($entities, $client, $forced);
    }

    /**
     * Clear the inner state of the library.
     *
     * This should free up the most of the allocated memory for the entities
     */
    public function clear()
    {
        $this->notificationManager->clear();
    }

    /**
     * Get alias from full class name.
     * Example: 'App\Entity\Product' => 'product'.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws EntityAliasNotFoundException
     */
    public function getAliasFromClass( $class)
    {
        return $this->aliasTranslator->getAliasFromClass($class);
    }

    /**
     * Get full class name from entity alias.
     * Example: 'product' => 'App\Entity\Product'.
     *
     * @param string $alias
     *
     * @return string
     *
     * @throws EntityAliasNotFoundException
     */
    public function getClassFromAlias( $alias)
    {
        return $this->aliasTranslator->getAliasFromClass($alias);
    }

    /**
     * Send all queued notificaitons.
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     */
    public function sendAllQueuedNotifications()
    {
        $this->notificationManager->sendBatch();
    }

    /**
     * Disable doctrine entity listener for the client. The notification for this client will not be created.
     *
     * IMPORTANT: This method call has an effect just for the process in which is used. It does not persist anywhere.
     *
     * @param ClientInterface $client
     */
    public function disableEntityListeningForClient(ClientInterface $client)
    {
        $event = new StopSynchronizationForClientEvent($client);
        $this->eventDispatcher->dispatch(StopSynchronizationForClientEvent::NAME, $event);
    }

    /**
     * Allow doctrine entity listener notifications for all client.
     *
     * This method is the opposite of the method disableEntityListeningForClient.
     *
     * IMPORTANT: This method call has an effect just for the process in which is used. It does not persist anywhere.
     */
    public function allowEntityListeningForAllClients()
    {
        $event = new StopSynchronizationForClientEvent(null);
        $this->eventDispatcher->dispatch(StopSynchronizationForClientEvent::NAME, $event);
    }
}
