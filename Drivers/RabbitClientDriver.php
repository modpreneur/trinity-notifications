<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:58
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
 * Class RabbitClientDriver
 *
 * @package Trinity\NotificationBundle\Drivers
 */
class RabbitClientDriver extends BaseDriver
{
    /** @var string */
    protected $clientId;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter          $entityConverter
     * @param NotificationUtils        $notificationUtils
     * @param BatchManager             $batchManager
     * @param string                   $clientId
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager,
        string $clientId
    ) {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $batchManager);

        $this->clientId = $clientId;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param array                       $params
     *
     * @return void
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function execute(NotificationEntityInterface $entity, ClientInterface $client = null, array $params = [])
    {
        if ($this->isEntityAlreadyProcessed($entity, 'server')) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity, 'server');

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
        $batch->addNotification($notification);
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
