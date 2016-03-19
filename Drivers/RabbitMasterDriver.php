<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 11.03.16
 * Time: 16:13
 */

namespace Trinity\NotificationBundle\Driver;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\RabbitMQ\ServerProducer;

class RabbitMasterDriver extends BaseDriver
{
    /** @var  ServerProducer */
    protected $producer;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface   $eventDispatcher
     * @param EntityConverter   $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param ServerProducer    $producer
     * @param TokenStorage      $tokenStorage
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        ServerProducer $producer,
        TokenStorage $tokenStorage
    ) {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $tokenStorage);

        $this->producer = $producer;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param array                       $params
     *
     * @return void
     */
    public function execute(NotificationEntityInterface $entity, ClientInterface $client = null, $params = [])
    {
        if ($this->isEntityAlreadyProcessed($entity)) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity);

        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        //send to all clients
        foreach ($entity->getClients() as $client) {
            //check if the client has enabled notifications
            if ($client->isNotificationEnabled()) {
                $entityArray["clientId"] = $client->getId();

                //get entity "name", e.g. "product", "user"
                $entityArray["entityName"] = $this->notificationUtils->getUrlPostfix($entity);

                //encode object array to JSON
                $json = $this->JSONEncodeObject($entityArray, $client->getSecret());

                $this->producer->publish($json, $client->getId());

                $entity->setNotificationStatus($client, 'unknown');
            }
        }
    }


    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName()
    {
        return "rabbit_master_driver";
    }
}