<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Driver\NotificationDriverInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\Server;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendEvent;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;


/**
 * Class NotificationManager.
 */
class NotificationManager
{
    /** @var  NotificationDriverInterface[] */
    private $drivers;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  ContainerInterface */
    protected $container;

    /** @var  string */
    protected $serverNotifyUri;

    /** @var  EntityManager */
    protected $entityManager;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContainerInterface $container
     * @param                          $serverNotifyUri
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container,
        $serverNotifyUri
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
        $this->serverNotifyUri = $serverNotifyUri;
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
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     *  Process notification.
     *
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     * @param bool $toClient
     *
     * @return array
     */
    public function send(NotificationEntityInterface $entity, $HTTPMethod = 'GET', $toClient = true)
    {
        if ($toClient) {
            $response = $this->sendToClient($entity, $HTTPMethod);
        } else {
            $response = $this->sendToServer($entity, $HTTPMethod);
        }

        return $response;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface $client
     * @return array
     */
    public function syncEntity(NotificationEntityInterface $entity, ClientInterface $client)
    {
        $responce = [];

        foreach ($this->drivers as $driver) {
            $responce[] = $this->executeEntityInDriver($entity, $driver, $client, 'PUT');
        }

        return $responce;
    }


    /**
     *  Send notification to client
     *
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToClient(NotificationEntityInterface $entity, $HTTPMethod = 'GET')
    {
        $response = [];

        /** @var ClientInterface[] $clients */
        $clients = $this->clientsToArray($entity->getClients());

        foreach ($this->drivers as $driver) {
            if ($clients) {
                foreach ($clients as $client) {
                    $response[] = $this->executeEntityInDriver($entity, $driver, $client, $HTTPMethod);

                }
            }
        }

        return $response;
    }


    /**
     *  Send notification to server
     *
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToServer(NotificationEntityInterface $entity, $HTTPMethod = 'GET')
    {
        $response = [];
        $server = new Server();
        $server->setNotificationUri($this->serverNotifyUri);

        foreach ($this->drivers as $driver) {
            // before send event
            $this->eventDispatcher->dispatch(Events::BEFORE_NOTIFICATION_SEND, new SendEvent($entity));

            //execute notification
            $resp = $driver->execute($entity, $server, ['HTTPMethod' => $HTTPMethod]);

            if ($resp) {
                $response[] = $resp;
            }

            // after
            $this->eventDispatcher->dispatch(Events::AFTER_NOTIFICATION_SEND, new SendEvent($entity));
        }

        return $response;
    }


    /**
     * Transform clients collection to array.
     *
     * @param ClientInterface|Collection|array $clientsCollection
     *
     * @return Object[]
     *
     * @throws ClientException
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
     * @return array|null
     */
    private function executeEntityInDriver(
        NotificationEntityInterface $entity,
        NotificationDriverInterface $driver,
        ClientInterface $client,
        $HTTPMethod = "POST"
    ) {
        // before send event
        $this->eventDispatcher
            ->dispatch(
                Events::BEFORE_NOTIFICATION_SEND,
                new SendEvent($entity)
            );

        //execute notification
        $resp = $driver
            ->execute(
                $entity,
                $client,
                ['HTTPMethod' => $HTTPMethod]
            );

        // after
        $this->eventDispatcher
            ->dispatch(
                Events::AFTER_NOTIFICATION_SEND,
                new SendEvent($entity)
            );


        if($this->entityManager){
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        }

        if ($resp) {
            return $resp;
        }
    }


}
