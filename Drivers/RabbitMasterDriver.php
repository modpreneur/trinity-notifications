<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 11.03.16
 * Time: 16:13.
 */
namespace Trinity\NotificationBundle\Drivers;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\EntityStatusLog;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\AnnotationsUtils;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\Notification\NotificationUtils;
use Trinity\NotificationBundle\Services\NotificationStatusManager;

/**
 * Class RabbitMasterDriver.
 */
class RabbitMasterDriver extends BaseDriver
{
    /** @var array */
    protected $messages;

    /** @var  NotificationStatusManager */
    protected $statusManager;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface  $eventDispatcher
     * @param EntityConverter           $entityConverter
     * @param NotificationUtils         $notificationUtils
     * @param BatchManager              $batchManager
     * @param NotificationStatusManager $statusManager
     * @param AnnotationsUtils          $annotationsUtils
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager,
        NotificationStatusManager $statusManager,
        AnnotationsUtils $annotationsUtils
    ) {
        parent::__construct(
            $eventDispatcher,
            $entityConverter,
            $notificationUtils,
            $batchManager,
            $annotationsUtils
        );

        $this->statusManager = $statusManager;
        $this->messages = [];
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $destinationClient
     * @param bool                        $force
     * @param array                       $changeSet
     * @param array                       $params
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function execute(
        NotificationEntityInterface $entity,
        ClientInterface $destinationClient = null,
        bool $force,
        array $changeSet = [],
        array $params = []
    ) {
        if ($this->isEntityAlreadyProcessed($entity, $destinationClient->getId())) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity, $destinationClient->getId());

        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        $changeSet = $this->prepareChangeset($entity, $changeSet);

        //execute for given client
        if ($destinationClient !== null) {
            $this->executeForClient($entity, $entityArray, $changeSet, $destinationClient, $force, $params);
        } else {
            //execute for all clients
            foreach ($entity->getClients() as $client) {
                $this->executeForClient($entity, $entityArray, $changeSet, $client, $force, $params);
            }
        }
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $entityArray
     * @param array                       $changeSet
     * @param ClientInterface             $client
     * @param bool                        $force
     * @param array                       $params
     */
    protected function executeForClient(
        NotificationEntityInterface $entity,
        array $entityArray,
        array $changeSet,
        ClientInterface $client,
        bool $force,
        array $params = []
    ) {
        //check if the client has enabled notifications
        if ($client->isNotified()) {
            $batch = $this->batchManager->createBatch($client->getId());
            //$batch is only pointer to the batch created and stored in BatchManager
            $batch->setSecretKey($client->getSecret());
            $batch->setDestination('client_'.$client->getId());

            $notification = new Notification();
            $notification->setData($entityArray);
            $notification->setMethod($params['HTTPMethod']);
            $notification->setMessageId($batch->getUid());
            $notification->setChangeSet($changeSet);
            $notification->setIsForced($force);
            $notification->setClientId($client->getId());
            //get entity "name", e.g. "product", "user"
            $notification->setEntityName($this->notificationUtils->getUrlPostfix($entity));

            $batch->addNotification($notification);
            $this->logNotification($notification);

            $this->statusManager->setEntityStatus(
                $entity,
                $client,
                time(),
                $batch->getUid(),
                EntityStatusLog::SYNCHRONIZATION_IN_PROGRESS
            );
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
