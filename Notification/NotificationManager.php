<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
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

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  BatchManager */
    protected $batchManager;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param BatchManager $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        BatchManager $batchManager
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->batchManager = $batchManager;
        $this->drivers = [];
    }


    /**
     * @param NotificationDriverInterface $driver
     */
    public function addDriver(NotificationDriverInterface $driver)
    {
        $this->drivers[] = $driver;
    }


    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Process notification.
     *
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     * @param bool $toClient
     *
     * @param array $options
     */
    public function send(NotificationEntityInterface $entity, string $HTTPMethod = 'GET', bool $toClient = true, array $options = [])
    {
        if ($toClient) {
            $this->sendToClient($entity, $HTTPMethod, $options);
        } else {
            $this->sendToServer($entity, $HTTPMethod, $options);
        }
    }


    /**
     * Send notifications in batch.
     */
    public function sendBatch()
    {
        $this->batchManager->send();
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface $client
     */
    public function syncEntity(NotificationEntityInterface $entity, ClientInterface $client)
    {
        foreach ($this->drivers as $driver) {
            $this->executeEntityInDriver($entity, $driver, $client, 'PUT');
        }
    }


    /**
     *  Send notification to client
     *
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     *
     * @param array $options
     */
    protected function sendToClient(NotificationEntityInterface $entity, $HTTPMethod = 'GET', array $options = [])
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
     * @param string $HTTPMethod
     * @param array $options
     * @return array
     */
    protected function sendToServer(NotificationEntityInterface $entity, $HTTPMethod = 'GET', array $options = [])
    {
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
    protected function clientsToArray($clientsCollection)
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
     * @param ClientInterface $client
     * @param string $HTTPMethod [POST, PUT, GET, DELETE, ...]
     *
     * @param array $options
     * @return array|null
     */
    private function executeEntityInDriver(
        NotificationEntityInterface $entity,
        NotificationDriverInterface $driver,
        ClientInterface $client,
        $HTTPMethod = "POST",
        array $options = []
    )
    {
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_DRIVER_EXECUTE)) {
            $beforeDriverExecute = new BeforeDriverExecuteEvent($entity);
            /** @var BeforeDriverExecuteEvent $beforeDriverExecute */
            $beforeDriverExecute = $this->eventDispatcher->dispatch(Events::BEFORE_DRIVER_EXECUTE, $beforeDriverExecute);
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
