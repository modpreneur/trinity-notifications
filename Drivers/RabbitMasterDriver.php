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

    /** @var  AnnotationsUtils */
    protected $annotationsUtils;

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
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $batchManager);

        $this->statusManager = $statusManager;
        $this->annotationsUtils = $annotationsUtils;
        $this->messages = [];
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $destinationClient
     * @param array                       $changeSet
     * @param array                       $params
     */
    public function execute(
        NotificationEntityInterface $entity,
        ClientInterface $destinationClient = null,
        array $changeSet = [],
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
            $this->executeForClient($entity, $entityArray, $changeSet, $destinationClient, $params);
        } else {
            //execute for all clients
            foreach ($entity->getClients() as $client) {
                $this->executeForClient($entity, $entityArray, $changeSet, $client, $params);
            }
        }
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $entityArray
     * @param array                       $changeSet
     * @param ClientInterface             $client
     * @param array                       $params
     */
    protected function executeForClient(
        NotificationEntityInterface $entity,
        array $entityArray,
        array $changeSet,
        ClientInterface $client,
        array $params = []
    ) {
        //check if the client has enabled notifications
        if ($client->isNotified()) {
            $notifiedProperties = array_flip(
                $this->annotationsUtils->getClassSourceAnnotation($entity)->getColumns()
            );

            //remove properties, which should not be sent
            $changeSet = $this->removeNotNotifiedProperties($changeSet, $notifiedProperties);

            //change indexes 0,1 in changeset to keys 'old' and 'new'
            $changeSet = $this->changeIndexesInChangeSet($changeSet);

            //get entity "name", e.g. "product", "user"
            $entityArray['entityName'] = $this->notificationUtils->getUrlPostfix($entity);

            $batch = $this->batchManager->createBatch($client->getId());
            //$batch is only pointer to the batch created and stored in BatchManager
            $batch->setSecretKey($client->getSecret());
            $batch->setDestination('client_'.$client->getId());

            $notification = new Notification();
            $notification->setData($entityArray);
            $notification->setMethod($params['HTTPMethod']);
            $notification->setMessageId($batch->getUid());
            $notification->setChangeSet($changeSet);

            $batch->addNotification($notification);

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
