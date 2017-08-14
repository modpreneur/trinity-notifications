<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\NotificationLogStorageInterface;

/**
 * Class NotificationStatusManager.
 */
class NotificationStatusManager extends AbstractNotificationStatusManager
{
    /** @var  NotificationLog */
    protected $lastNotificationLog;

    /** @var  EntityAliasTranslator */
    protected $aliasTranslator;

    /** @var NotificationLogStorageInterface */
    protected $logStorage;

    /**
     * NotificationStatusManager constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityAliasTranslator $aliasTranslator
     * @param NotificationLogStorageInterface $logStorage
     */
    public function __construct(
        LoggerInterface $logger,
        EntityAliasTranslator $aliasTranslator,
        NotificationLogStorageInterface $logStorage
    ) {
        parent::__construct($logger);

        $this->aliasTranslator = $aliasTranslator;
        $this->logStorage = $logStorage;
    }

    /**
     * Get status message for the notification batch in which was the notification.
     *
     * @param Notification $notification
     *
     * @return StatusMessage|null
     */
    public function getStatusMessageFromNotification(Notification $notification)
    {
        return $this->logStorage->getStatusMessageForMessage($notification->getMessageId());
    }

    /**
     * Get status for the notification.
     *
     * @param Notification $notification
     *
     * @return NotificationStatus
     *
     * @throws \Exception
     */
    public function getNotificationStatus(Notification $notification)
    {
        if ($this->lastNotificationLog === null || $notification->getUid() !== $this->lastNotificationLog->getUid()) {
            //the NotificationLog for the notification was not queued yet
            $result = $this->logStorage->getNotificationLog($notification->getUid());

            if ($result === null) {
                // TODO @JakubFajkus some specific Exception
                throw new \Exception('No NotificationLog with id:'.$notification->getUid().' found!');
            }

            $this->lastNotificationLog = $result;
        }

        $status = new NotificationStatus();
        $status->setStatus($this->lastNotificationLog->getStatus());
        $status->setNotificationId($this->lastNotificationLog->getUid());
        $status->setMessage($this->lastNotificationLog->getStatusMessage());

        return $status;
    }

    /**
     * Get last notification for the entity.
     *
     * @param NotificationEntityInterface $entity
     * @param int|ClientInterface         $client
     *
     * @return null|Notification
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     */
    public function getLastNotification(NotificationEntityInterface $entity, $client)
    {
        $clientId = $this->getClientId($client);
        $entityName = $this->aliasTranslator->getAliasFromClass($this->getEntityClass($entity));

        //query notifications storage and look for clientId && entityName && entityId and grab the newest one
        return $this->logStorage->getLastNotificationLogForEntity($entityName, $entity->getId(), $clientId);
    }
}
