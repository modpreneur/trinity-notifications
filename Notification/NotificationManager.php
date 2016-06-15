<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Drivers\NotificationDriverInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\Server;
use Trinity\NotificationBundle\Event\AfterDriverExecuteEvent;
use Trinity\NotificationBundle\Event\BeforeDriverExecuteEvent;
use Trinity\NotificationBundle\Event\Events;

/**
 * Class NotificationManager.
 */
class NotificationManager
{
    /** @var  NotificationDriverInterface[] */
    private $drivers;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  string */
    protected $serverNotifyUri;

    /** @var  BatchManager */
    protected $batchManager;

    /** @var  array */
    protected $queuedNotifications;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param BatchManager             $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
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
     * @param string                      $HTTPMethod
     * @param bool                        $toClients
     * @param array                       $options
     */
    public function queueEntity(
        NotificationEntityInterface $entity,
        string $HTTPMethod = 'GET',
        bool $toClients = true,
        array $options = []
    ) {
        $array = [];
        $array['entity'] = $entity;
        $array['HTTPMethod'] = $HTTPMethod;
        $array['toClients'] = $toClients;
        $array['options'] = $options;

        $this->queuedNotifications[] = $array;
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
                    $queuedNotification['HTTPMethod'],
                    $queuedNotification['options']
                );
            } else {
                $this->sendToServer(
                    $queuedNotification['entity'],
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
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function syncEntity(NotificationEntityInterface $entity, ClientInterface $client)
    {
        foreach ($this->drivers as $driver) {
            $this->executeEntityInDriver($entity, $driver, $client, 'PUT');
        }

        $this->batchManager->sendAll();
        $this->clear();

    }


    /**
     * @param NotificationEntityInterface[] $entities
     * @param ClientInterface               $client
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function syncEntities(array $entities, ClientInterface $client)
    {
        foreach ($this->drivers as $driver) {
            foreach ($entities as $entity) {
                $this->executeEntityInDriver($entity, $driver, $client, 'PUT');
            }
        }

        $this->batchManager->sendAll();
        $this->clear();

    }


    /**
     * Clear the queue notifications and BatchManager to prevent sending the notifications twice
     */
    public function clear()
    {
        $this->queuedNotifications = [];
        $this->batchManager->clear();
    }


    /**
     *  Send notification to client
     *
     * @param NotificationEntityInterface $entity
     * @param string                      $HTTPMethod
     * @param array                       $options
     */
    protected function sendToClients(NotificationEntityInterface $entity, $HTTPMethod = 'GET', array $options = [])
    {
        /** @var ClientInterface[] $clients */
        $clients = $this->clientsToArray($entity->getClients());

        foreach ($this->drivers as $driver) {
            if ($clients) {
                foreach ($clients as $client) {
                    $this->executeEntityInDriver($entity, $driver, $client, $HTTPMethod, $options);
                }
            }
        }
    }


    /**
     * Send notification to server
     *
     * @param NotificationEntityInterface $entity
     * @param string                      $HTTPMethod
     * @param array                       $options
     */
    protected function sendToServer(
        NotificationEntityInterface $entity,
        $HTTPMethod = 'GET',
        array $options = []
    ) {
        foreach ($this->drivers as $driver) {
            $this->executeEntityInDriver($entity, $driver, new Server(), $HTTPMethod, $options);
        }
    }


    /**
     * Transform clients collection to array.
     *
     * @param ClientInterface|Collection|array $clientsCollection
     *
     * @return Object[]
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
     * @param string                      $HTTPMethod [POST, PUT, GET, DELETE, ...]
     * @param array                       $options
     */
    private function executeEntityInDriver(
        NotificationEntityInterface $entity,
        NotificationDriverInterface $driver,
        ClientInterface $client,
        $HTTPMethod = 'POST',
        array $options = []
    ) {
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_DRIVER_EXECUTE)) {
            $beforeDriverExecute = new BeforeDriverExecuteEvent($entity);
            /** @var BeforeDriverExecuteEvent $beforeDriverExecute */
            $beforeDriverExecute = $this->eventDispatcher->dispatch(
                Events::BEFORE_DRIVER_EXECUTE,
                $beforeDriverExecute
            );
            $entity = $beforeDriverExecute->getEntity();
        }

        //execute notification
        $driver
            ->execute(
                $entity,
                $client,
                ['HTTPMethod' => $HTTPMethod, 'options' => $options]
            );

        if ($this->eventDispatcher->hasListeners(Events::AFTER_DRIVER_EXECUTE)) {
            $afterDriverExecute = new AfterDriverExecuteEvent($entity);
            /** @var AfterDriverExecuteEvent $afterDriverExecute */
            $this->eventDispatcher->dispatch(Events::AFTER_DRIVER_EXECUTE, $afterDriverExecute);
        }
    }
}
