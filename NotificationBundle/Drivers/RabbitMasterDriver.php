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
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\RabbitMQ\ServerProducer;

class RabbitMasterDriver extends BaseDriver
{
    /** @var  ServerProducer */
    protected $producer;

    protected $messages;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param ServerProducer $producer
     * @param TokenStorage $tokenStorage
     * @param BatchManager $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        ServerProducer $producer,
        TokenStorage $tokenStorage,
        BatchManager $batchManager
    )
    {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $tokenStorage, $batchManager);

        $this->producer = $producer;
        $this->messages = [];

        $this->batchManager->setProducer($producer);
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface $client
     * @param array $params
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
                //get entity "name", e.g. "product", "user"
                $entityArray["entityName"] = $this->notificationUtils->getUrlPostfix($entity);

                $batch = $this->batchManager->createBatch($client->getId());
                $batch->setClientSecret($client->getSecret());
                $notification = new Notification();
                $notification->setData($entityArray);
                $notification->setMethod($params["HTTPMethod"]);
                $notification->setBatchId($batch->getUId());
                $batch->addNotification($notification);

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