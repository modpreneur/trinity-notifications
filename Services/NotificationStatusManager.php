<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\Bundle\LoggerBundle\Services\ElasticLogService;
use Trinity\Bundle\LoggerBundle\Services\ElasticReadLogService;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\EntityStatus;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationStatus;

/**
 * Class NotificationStatusManager.
 */
abstract class NotificationStatusManager
{
    /** @var  ElasticReadLogService */
    protected $elasticReader;

    /** @var  ElasticLogService */
    protected $elasticWriter;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * NotificationStatusManager constructor.
     *
     * @param ElasticReadLogService $elasticReader
     * @param ElasticLogService     $elasticWriter
     * @param LoggerInterface       $logger
     */
    public function __construct(
        ElasticReadLogService $elasticReader,
        ElasticLogService $elasticWriter,
        LoggerInterface $logger
    ) {
        $this->elasticReader = $elasticReader;
        $this->elasticWriter = $elasticWriter;
        $this->logger = $logger;
    }

    /**
     * Get last notification for the entity.
     *
     * @param NotificationEntityInterface $entity
     * @param int|ClientInterface         $client
     *
     * @return null|Notification
     */
    abstract public function getLastNotification(NotificationEntityInterface $entity, $client);

    /**
     * Get status message for the notification batch in which was the notification.
     *
     * @param Notification $notification
     *
     * @return StatusMessage|null
     */
    abstract public function getStatusMessageFromNotification(Notification $notification);

    /**
     * Get status for the notification.
     *
     * @param Notification $notification
     *
     * @return NotificationStatus
     */
    abstract public function getNotificationStatus(Notification $notification);

    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface|int         $client Instance of ClientInterface or client id
     *
     * @return null|EntityStatus
     *
     * @throws \InvalidArgumentException
     */
    public function getEntityStatus(NotificationEntityInterface $entity, $client)
    {
        $clientId = $this->getClientId($client);

        $entityStatus = new EntityStatus();
        $entityStatus->setClientId($clientId);
        $entityStatus->setEntityClass(get_class($entity));
        //todo: change to serverId on client? add method getNotificationId to EntityInterface?
        $entityStatus->setEntityId($entity->getId());

        $lastNotification = $this->getLastNotification($entity, $clientId);
        if (null === $lastNotification) {
            $entityStatus->setStatus(EntityStatus::NOT_SYNCHRONIZED);
            $entityStatus->setNotificationId('');
            $entityStatus->setMessageUid('');
            $entityStatus->setStatusMessage('No notification found');
            $entityStatus->setChangedAt(0);

            return $entityStatus;
        }

        $entityStatus->setNotificationId($lastNotification->getUid());
        $entityStatus->setMessageUid($lastNotification->getMessageId());
        $entityStatus->setChangedAt($lastNotification->getCreatedAt());

        $notificationStatus = $this->getNotificationStatus($lastNotification);

        if ($notificationStatus->getStatus() === NotificationStatus::STATUS_OK) {
            $entityStatus->setStatus(EntityStatus::SYNCHRONIZED);
            $entityStatus->setStatusMessage($notificationStatus->getMessage());

            return $entityStatus;
        } elseif ($notificationStatus->getStatus() === NotificationStatus::STATUS_ERROR) {
            $entityStatus->setStatus(EntityStatus::SYNCHRONIZATION_ERROR);
            $entityStatus->setStatusMessage($notificationStatus->getMessage());

            return $entityStatus;
        } elseif ($notificationStatus->getStatus() === NotificationStatus::STATUS_SENT) {
            $statusMessage = $this->getStatusMessageFromNotification($lastNotification);

            if ($statusMessage !== null) {
                $statusMessage = StatusMessage::createFromMessage($statusMessage);
            } else {
                $entityStatus->setStatus(EntityStatus::SYNCHRONIZATION_IN_PROGRESS);

                $this->logger->info('Trying to get status for entity of class'.get_class($entity)
                    .' with id '.$entity->getId().'. A message with the notification has been sent'
                    .', but there is no status message.');

                return $entityStatus;
            }

            if ($statusMessage->isOkay()) {
                $this->logger->emergency('Trying to get status for entity of class'.get_class($entity)
                    .' with id '.$entity->getId().'. A message with the notification has been sent'
                    .', it was confirmed by the other side but the notification itself has no status.');

                $entityStatus->setStatus(EntityStatus::SYNCHRONIZATION_ERROR);

                return $entityStatus;
            } else {
                $entityStatus->setStatus(EntityStatus::SYNCHRONIZATION_ERROR);

                return $entityStatus;
            }
        } else {
            //return not synchronized and log as emergency
            $entityStatus->setStatus(EntityStatus::NOT_SYNCHRONIZED);

            $this->logger->emergency('Trying to get status for entity of class'.get_class($entity)
                .' with id '.$entity->getId().'. The notification with id:'.$lastNotification->getUid()
                .' has an invalid status. The status is: '.$notificationStatus->getStatus());

            return $entityStatus;
        }
    }

    /**
     * Get entity class.
     *
     * @param NotificationEntityInterface $entity
     *
     * @return string
     */
    protected function getEntityClass(NotificationEntityInterface $entity)
    {
        //fix Doctrine proxy
        return str_replace('Proxies\__CG__\\', '', get_class($entity));
    }

    /**
     * @param ClientInterface|int $client
     *
     * @return int
     */
    protected function getClientId($client)
    {
        if ($client instanceof ClientInterface) {
            return $client->getId();
        } else {
            return $client;
        }
    }
}
