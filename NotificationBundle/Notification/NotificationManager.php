<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Driver\NotificationDriverInterface;
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
     *  Process notification.
     *
     * @param object $entity
     * @param string $HTTPMethod
     * @param bool $toClient
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    public function send($entity, $HTTPMethod = 'GET', $toClient = true)
    {
        if ($toClient) {
            $response = $this->sendToClient($entity, $HTTPMethod);
        } else {
            $response = $this->sendToServer($entity, $HTTPMethod);
        }

        return $response;
    }


    /**
     * @param object $entity
     * @return array
     */
    public function syncEntity($entity){
        return $this->send($entity, 'PUT');
    }


    /**
     *  Send notification to client
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToClient($entity, $HTTPMethod = 'GET')
    {
        $response = [];

        /** @var ClientInterface[] $clients */
        $clients = $this->clientsToArray($entity->getClients());

        foreach ($this->drivers as $driver) {
            if ($clients) {
                foreach ($clients as $client) {
                    // before send event
                    $this->eventDispatcher->dispatch(Events::BEFORE_NOTIFICATION_SEND, new SendEvent($entity));

                    //execute notification
                    $resp = $driver->execute($entity, $client, ['HTTPMethod' => $HTTPMethod]);

                    if ($resp) {
                        $response[] = $resp;
                    }

                    // after
                    $this->eventDispatcher->dispatch(Events::AFTER_NOTIFICATION_SEND, new SendEvent($entity));
                }
            }
        }

        return $response;
    }


    /**
     *  Send notification to server
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToServer($entity, $HTTPMethod = 'GET')
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

}
