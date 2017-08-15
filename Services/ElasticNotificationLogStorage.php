<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\NotificationBundle\Entity\MessageLog;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\ElasticLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\ElasticReadLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\NotificationLogStorageInterface;

/**
 * Class ElasticNotificationLogStorage
 *
 * Storage using the elastic search.
 */
class ElasticNotificationLogStorage implements NotificationLogStorageInterface
{
    /** @var ElasticReadLogServiceInterface */
    protected $reader;

    /** @var ElasticLogServiceInterface */
    protected $logger;

    /** @var LoggerInterface */
    protected $monolog;

    /**
     * ElasticNotificationLogStorage constructor.
     *
     * @param ElasticLogServiceInterface $esLogger
     * @param ElasticReadLogServiceInterface $readLog
     * @param LoggerInterface $monolog
     */
    public function __construct(
        ElasticLogServiceInterface $esLogger,
        ElasticReadLogServiceInterface $readLog,
        LoggerInterface $monolog
    ) {
        $this->reader = $esLogger;
        $this->logger = $readLog;
        $this->monolog = $monolog;
    }

    /**
     * @param string $messageUid
     *
     * @return StatusMessage|null
     */
    public function getStatusMessageForMessage(string $messageUid)
    {
        //get the status message message, which was a reaction to the message with $messageUid
        $query['query']['bool']['must'][] = ['match' => ['parentMessageUid' => $messageUid]];
        $query['query']['bool']['must'][] = ['match' => ['type' => StatusMessage::MESSAGE_TYPE]];
        //$statusMessage = query $batch
        $result = $this->getResult(
            'MessageLog',
            $query,
            1
        );

        //return the first result or null
        return count($result) > 0 ? $result[0] : null;
    }

    /**
     * @param string $notificationUid
     *
     * @return NotificationLog|null
     */
    public function getNotificationLog(string $notificationUid)
    {
        $query['query']['bool']['must'][] = ['match' => ['uid' => $notificationUid]];
        $result = $this->getResult(NotificationLog::TYPE, $query, 1);

        //return the first result or null
        return count($result) > 0 ? $result[0] : null;

    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param int $clientId
     * @return null|NotificationLog
     */
    public function getLastNotificationLogForEntity(string $entityName, int $entityId, int $clientId)
    {
        $query['query']['bool']['must'][] = ['match' => ['entityName' => $entityName]];
        $query['query']['bool']['must'][] = ['match' => ['entityId' => $entityId]];
        $query['query']['bool']['must'][] = ['match' => ['clientId' => $clientId]];

        $result = $this->getResult(NotificationLog::TYPE, $query, 1);

        //return the first result or null
        return count($result) > 0 ? $result[0] : null;
    }

    /**
     * @param NotificationStatus[] $statuses
     *
     * @return void
     */
    public function updateNotificationStatuses(array $statuses)
    {
        foreach ($statuses as $status) {
            $notificationLog = $this->getNotificationLogForNotification($status->getNotificationId());

            if ($notificationLog !== null) {
                $this->logger->update(
                    NotificationLog::TYPE,
                    $notificationLog->getId(),
                ['status', 'statusMessage', 'extra'],
                [$status->getStatus(), $status->getMessage(), json_encode($status->getExtra())]
                );
            }
        }
    }

    /**
     * @param MessageLog $message
     * @return void
     */
    public function createMessageLog(MessageLog $message)
    {
        $this->logger->writeIntoAsync('MessageLog', $message);
    }

    /**
     * Set status of the message with $messageId to $status.
     *
     * @param string $messageId Message id
     * @param string $status Status of the message(ok, error)
     * @param string $statusMessage Additional message to the status(practically additional information for 'error'
     *                              status).
     *
     * @return void
     */
    public function setMessageStatus(string $messageId, string $status, string $statusMessage)
    {
        $query['bool']['must'][] = ['match' => ['uid' => $messageId]];

        $entities = $this->getResult(MessageLog::getLogName(), ['query' => $query], 1);

        if (count($entities) === 1) {
            /** @var MessageLog $entity */
            $entity = $entities[0];
            $elasticKey = $entity->getId();

            $this->logger->update(MessageLog::getLogName(), $elasticKey, ['status', 'error'], [$status, $statusMessage]);
        }
    }

    /**
     * @param NotificationLog $log
     *
     * @return void
     */
    public function logNotification(NotificationLog $log)
    {
        $this->logger->writeIntoAsync(NotificationLog::getLogName(), $log);
    }


    /**
     * @param string $type
     * @param array  $query
     * @param int    $count
     *
     * @return array
     */
    protected function getResult(string $type, array $query, int $count)
    {
        try {
            $result = $this->reader->getMatchingEntities(
                $type,
                $query,
                $count,
                [],
                [['createdAt' => ['order' => 'desc']]]
            );
        } catch (\Throwable $e) {
            $this->monolog->error($e);

            return [];
        }

        return $result;
    }

    /**
     * @param string $notificationUid
     *
     * @return NotificationLog|null
     */
    protected function getNotificationLogForNotification(string $notificationUid)
    {
        $query['query']['bool']['must'][] = ['match' => ['uid' => $notificationUid]];

        $result = $this->getResult(NotificationLog::TYPE, $query, 1);

        //return the first result or null
        $result = count($result) > 0 ? $result[0] : null;

        if ($result === null) {
            $this->monolog->emergency('Trying to set notification status for notification with id:'
                .$notificationUid.' but the notification does not exist');
            //the result is null and the getId would fail!
            return null;
        }

        return $result;
    }
}