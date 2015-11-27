<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Driver\INotificationDriver;
use Trinity\NotificationBundle\Entity\Necktie;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendEvent;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    protected $necktieNotifyUri;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContainerInterface       $container
     * @param                          $necktieNotifyUri
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ContainerInterface $container, $necktieNotifyUri)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
        $this->necktieNotifyUri = $necktieNotifyUri;
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
        if($toClient)
        {
            $response = $this->sendToClient($entity, $HTTPMethod);
        }
        else
        {
            $response = $this->sendToNecktie($entity, $HTTPMethod);
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

        foreach($this->drivers as $driver){
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
     *  Send notification to necktie
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function sendToNecktie($entity, $HTTPMethod = 'GET')
    {
        $response = [];
        $necktie = new Necktie();
        $necktie->setNotificationUri($this->necktieNotifyUri);

        foreach($this->drivers as $driver){
            // before send event
            $this->eventDispatcher->dispatch(Events::BEFORE_NOTIFICATION_SEND, new SendEvent($entity));

            //execute notification
            $resp = $driver->execute($entity, $necktie, ['HTTPMethod' => $HTTPMethod]);

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
