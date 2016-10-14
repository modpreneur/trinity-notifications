<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Drivers\NotificationDriverInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\Server;

/**
 * Class NotificationManager.
 */
class NotificationManager
{
    /** @var  NotificationDriverInterface[] */
    protected $drivers;

    /** @var  NotificationEventDispatcher */
    protected $eventDispatcher;

    /** @var  string */
    protected $serverNotifyUri;

    /** @var  BatchManager */
    protected $batchManager;

    /** @var  array */
    protected $queuedNotifications;

    /** @var  ClientInterface|null */
    protected $stoppedForClient;

    /**
     * NotificationManager constructor.
     *
     * @param NotificationEventDispatcher $eventDispatcher
     * @param BatchManager                $batchManager
     */
    public function __construct(
        NotificationEventDispatcher $eventDispatcher,
        BatchManager $batchManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->batchManager = $batchManager;
        $this->drivers = [];
        $this->queuedNotifications = [];
    }

    /**
     * @param NotificationDriverInterface $driver
     */
    public function addDriver(NotificationDriverInterface $driver)
    {
        $this->drivers[] = $driver;
    }

    /**
     * Queue entity to be processed.
     * Internally stores the pointer to the entity to an array.
     *
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet
     * @param bool                        $force
     * @param string                      $HTTPMethod
     * @param bool                        $toClients
     * @param array                       $options
     */
    public function queueEntity(
        NotificationEntityInterface $entity,
        array $changeSet,
        bool $force,
        string $HTTPMethod = 'GET',
        bool $toClients = true,
        array $options = []
    ) {
        $this->queuedNotifications[] = [
            'entity' => $entity,
            'changeSet' => $changeSet,
            'HTTPMethod' => $HTTPMethod,
            'toClients' => $toClients,
            'options' => $options,
            'force' => $force,
        ];
    }

    /**
     * Send notifications in batch.
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function sendBatch()
    {
        foreach ($this->queuedNotifications as $queuedNotification) {
            if ($queuedNotification['toClients']) {
                $this->sendToClients(
                    $queuedNotification['entity'],
                    $queuedNotification['force'],
                    $queuedNotification['changeSet'],
                    $queuedNotification['HTTPMethod'],
                    $queuedNotification['options']
                );
            } else {
                $this->sendToServer(
                    $queuedNotification['entity'],
                    $queuedNotification['force'],
                    $queuedNotification['changeSet'],
                    $queuedNotification['HTTPMethod'],
                    $queuedNotification['options']
                );
            }
        }

        $this->batchManager->sendAll();
        $this->clear();
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param bool                        $force
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     */
    public function syncEntity(NotificationEntityInterface $entity, ClientInterface $client, bool $force)
    {
        foreach ($this->drivers as $driver) {
            $this->executeEntityInDriver($entity, $driver, $client, $force, [], 'PUT');
        }

        $this->batchManager->sendAll();
        $this->clear();
    }

    /**
     * @param NotificationEntityInterface[] $entities
     * @param ClientInterface               $client
     * @param bool                          $force
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     */
    public function syncEntities(array $entities, ClientInterface $client, bool $force)
    {
        foreach ($this->drivers as $driver) {
            foreach ($entities as $entity) {
                $this->executeEntityInDriver($entity, $driver, $client, $force, [], 'PUT');
            }
        }

        $this->batchManager->sendAll();
        $this->clear();
    }

    /**
     * Clear the queued notifications and BatchManager to prevent sending the notifications twice.
     */
    public function clear()
    {
        $this->queuedNotifications = [];
        $this->batchManager->clear();
    }

    /**
     * @param ClientInterface|null $client
     */
    public function disableNotification(ClientInterface $client = null)
    {
        $this->stoppedForClient = $client;
    }

    /**
     *  Send notification to client.
     *
     * @param NotificationEntityInterface $entity
     * @param bool                        $force
     * @param array                       $changeSet
     * @param string                      $HTTPMethod
     * @param array                       $options
     */
    protected function sendToClients(
        NotificationEntityInterface $entity,
        bool $force,
        array $changeSet,
        $HTTPMethod = 'PUT',
        array $options = []
    ) {
        /** @var ClientInterface[] $clients */
        $clients = $this->clientsToArray($entity->getClients());

        foreach ($this->drivers as $driver) {
            if ($clients) {
                foreach ($clients as $client) {
                    if ($client === $this->stoppedForClient) {
                        continue;
                    }
                    $this->executeEntityInDriver($entity, $driver, $client, $force, $changeSet, $HTTPMethod, $options);
                }
            }
        }
    }

    /**
     * Send notification to server.
     *
     * @param NotificationEntityInterface $entity
     * @param bool                        $force
     * @param array                       $changeSet
     * @param string                      $HTTPMethod
     * @param array                       $options
     */
    protected function sendToServer(
        NotificationEntityInterface $entity,
        bool $force,
        array $changeSet,
        $HTTPMethod = 'GET',
        array $options = []
    ) {
        foreach ($this->drivers as $driver) {
            $server = new Server();
            $server->setId(0);
            $server->setName('client');
            $this->executeEntityInDriver($entity, $driver, $server, $force, $changeSet, $HTTPMethod, $options);
        }
    }

    /**
     * Transform clients collection to array.
     *
     * @param ClientInterface|Collection|array $clientsCollection
     *
     * @return object[]
     */
    protected function clientsToArray($clientsCollection) : array
    {
        $clients = [];

        if ($clientsCollection instanceof Collection) {
            $clients = $clientsCollection->toArray();
        } elseif ($clientsCollection instanceof ClientInterface) {
            $clients[] = $clientsCollection;
        } elseif (is_array($clientsCollection)) {
            $clients = $clientsCollection;
        }

        return $clients;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param NotificationDriverInterface $driver
     * @param ClientInterface             $client
     * @param bool                        $force
     * @param array                       $changeSet
     * @param string                      $HTTPMethod [POST, PUT, GET, DELETE, ...]
     * @param array                       $options
     */
    private function executeEntityInDriver(
        NotificationEntityInterface $entity,
        NotificationDriverInterface $driver,
        ClientInterface $client,
        bool $force,
        array $changeSet,
        $HTTPMethod = 'POST',
        array $options = []
    ) {
        $this->eventDispatcher->dispatchBeforeDriverExecuteEvent($entity);

        //execute notification
        $driver
            ->execute(
                $entity,
                $client,
                $force,
                $changeSet,
                ['HTTPMethod' => $HTTPMethod, 'options' => $options]
            );

        $this->eventDispatcher->dispatchAfterDriverExecuteEvent($entity);
    }
}
