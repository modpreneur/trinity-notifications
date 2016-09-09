<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:58.
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
 * Class RabbitClientDriver.
 */
class RabbitClientDriver extends BaseDriver
{
    /** @var string */
    protected $clientId;

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
     * @param string                    $clientId
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager,
        NotificationStatusManager $statusManager,
        AnnotationsUtils $annotationsUtils,
        string $clientId
    ) {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $batchManager);

        $this->statusManager = $statusManager;

        $this->clientId = $clientId;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param array                       $changeSet
     * @param array                       $params
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function execute(
        NotificationEntityInterface $entity,
        ClientInterface $client = null,
        bool $force,
        array $changeSet = [],
        array $params = []
    ) {
        if ($this->isEntityAlreadyProcessed($entity, 'server')) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity, 'server');

        $notifiedProperties = array_flip(
            $this->annotationsUtils->getClassSourceAnnotation($entity)->getColumns()
        );

        //remove properties, which should not be sent
        $changeSet = $this->removeNotNotifiedProperties($changeSet, $notifiedProperties);

        //change indexes 0,1 in changeset to keys 'old' and 'new'
        $changeSet = $this->changeIndexesInChangeSet($changeSet);

        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        //get entity "name", e.g. "product", "user"
        $entityArray['entityName'] = $this->notificationUtils->getUrlPostfix($entity);

        $batch = $this->batchManager->createBatch($this->clientId);
        //$batch is only pointer to the batch created and stored in BatchManager
        $batch->setDestination('server');

        $notification = new Notification();
        $notification->setData($entityArray);
        $notification->setMethod($params['HTTPMethod']);
        $notification->setMessageId($batch->getUid());
        $notification->setChangeSet($changeSet);
        $notification->setIsForced($force);
        $notification->setClientId($this->clientId);

        $batch->addNotification($notification);

        $this->statusManager->setEntityStatus(
            $entity,
            $client,
            time(),
            $batch->getUid(),
            EntityStatusLog::SYNCHRONIZATION_IN_PROGRESS
        );
    }

    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName() : string
    {
        return 'rabbit_server_driver';
    }
}
