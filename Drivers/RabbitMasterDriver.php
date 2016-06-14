<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 11.03.16
 * Time: 16:13
 */

namespace Trinity\NotificationBundle\Drivers;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\Notification\NotificationUtils;

/**
 * Class RabbitMasterDriver
 *
 * @package Trinity\NotificationBundle\Drivers
 */
class RabbitMasterDriver extends BaseDriver
{
    /** @var array */
    protected $messages;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter          $entityConverter
     * @param NotificationUtils        $notificationUtils
     * @param BatchManager             $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager
    ) {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $batchManager);

        $this->messages = [];
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $destinationClient
     * @param array                       $params
     *
     * @return void
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function execute(
        NotificationEntityInterface $entity,
        ClientInterface $destinationClient = null,
        array $params = []
    ) {
        if ($this->isEntityAlreadyProcessed($entity, $destinationClient->getId())) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity, $destinationClient->getId());

        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        //execute for given client
        if ($destinationClient !== null) {
            $this->executeForClient($entity, $entityArray, $destinationClient, $params);
        } else {
            //execute for all clients
            foreach ($entity->getClients() as $client) {
                $this->executeForClient($entity, $entityArray, $client, $params);
            }
        }


    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $entityArray
     * @param ClientInterface             $client
     * @param array                       $params
     */
    protected function executeForClient(
        NotificationEntityInterface $entity,
        array $entityArray,
        ClientInterface $client,
        array $params = []
    ) {
        //check if the client has enabled notifications
        if ($client->isNotified()) {
            //get entity "name", e.g. "product", "user"
            $entityArray['entityName'] = $this->notificationUtils->getUrlPostfix($entity);


            $batch = $this->batchManager->createBatch($client->getId());
            //$batch is only pointer to the batch created and stored in BatchManager
            $batch->setSecretKey($client->getSecret());
            $batch->setDestination('client_' . $client->getId());
            
            $notification = new Notification();
            $notification->setData($entityArray);
            $notification->setMethod($params['HTTPMethod']);
            $notification->setMessageId($batch->getUid());
            $batch->addNotification($notification);
        }
    }


    /**
     * Return name of the driver.
     *
     * @return string
     */
    public function getName() : string
    {
        return 'rabbit_master_driver';
    }
}
