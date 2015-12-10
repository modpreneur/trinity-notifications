<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Driver\INotificationDriver;
use Trinity\NotificationBundle\Entity\Master;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendEvent;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;

/**
 * Class NotificationManager.
 */
class NotificationManager
{
    /** @var  INotificationDriver[] */
    private $drivers;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  ContainerInterface */
    protected $container;

    /** @var  string  */
    protected $masterNotifyUri;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContainerInterface       $container
     * @param                          $masterNotifyUri
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ContainerInterface $container, $masterNotifyUri)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
        $this->masterNotifyUri = $masterNotifyUri;
        $this->drivers = [];
    }


    /**
     * @param INotificationDriver $driver
     */
    public function addDriver(INotificationDriver $driver)
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
            $response = $this->sendToMaster($entity, $HTTPMethod);
        }
        return $response;
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

        /** @var IClient[] $clients */
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
     *  Send notification to master
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToMaster($entity, $HTTPMethod = 'GET')
    {
        $response = [];
        $master = new Master();
        $master->setNotificationUri($this->masterNotifyUri);

        foreach ($this->drivers as $driver) {
            // before send event
            $this->eventDispatcher->dispatch(Events::BEFORE_NOTIFICATION_SEND, new SendEvent($entity));

            //execute notification
            $resp = $driver->execute($entity, $master, ['HTTPMethod' => $HTTPMethod]);

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
     * @param IClient|Collection|array $clientsCollection
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
        } elseif ($clientsCollection instanceof IClient) {
            $clients[] = $clientsCollection;
        } elseif (is_array($clientsCollection)) {
            $clients = $clientsCollection;
        }

        return $clients;
    }

}
