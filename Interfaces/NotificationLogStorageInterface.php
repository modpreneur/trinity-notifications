<?php

namespace Trinity\NotificationBundle\Interfaces;

use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;

/**
 * Interface NotificationLogStorageInterface
 *
 * @package Trinity\NotificationBundle\Interfaces
 *
 * Interface to access the persistence level of the logs.
 */
interface NotificationLogStorageInterface
{
    /**
     * @param string $messageUid
     *
     * @return StatusMessage|null
     */
    public function getStatusMessageForMessage(string $messageUid);

    /**
     * @param string $notificationUid
     *
     * @return NotificationLog|null
     */
    public function getNotificationLog(string $notificationUid);

    /**
     * @param string $entityName
     * @param int $entityId
     * @param int $clientId
     * @return null|NotificationLog
     */
    public function getLastNotificationLogForEntity(string $entityName, int $entityId, int $clientId);

    /**
     * @param NotificationStatus[] $statuses
     *
     * @return void
     */
    public function updateNotificationStatuses(array $statuses);


    /**
     * @param Message $message
     * @return void
     */
    public function createMessageLog(Message $message);

    /**
     * Set status of the message with $messageId to $status.
     *
     * @param string $messageId     Message id
     * @param string $status        Status of the message(ok, error)
     * @param string $statusMessage Additional message to the status(practically additional information for 'error'
     *                              status).
     *
     * @return void
     */
    public function setMessageStatus(
        string $messageId,
        string $status,
        string $statusMessage
    );

    /**
     * @param NotificationLog $log
     *
     * @return void
     */
    public function logNotification(NotificationLog $log);
}