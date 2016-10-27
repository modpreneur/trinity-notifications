<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\Bundle\LoggerBundle\Services\ElasticLogServiceWithTtl;
use Trinity\Bundle\LoggerBundle\Services\ElasticReadLogService;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;

/**
 * Class NotificationLogger.
 */
class NotificationLogger implements NotificationLoggerInterface
{
    /** @var ElasticLogServiceWithTtl */
    protected $esLogger;

    /** @var ElasticReadLogService */
    protected $readLog;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * MessageLogger constructor.
     *
     * @param ElasticLogServiceWithTtl $elasticLogger
     * @param ElasticReadLogService    $readLog
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ElasticLogServiceWithTtl $elasticLogger,
        ElasticReadLogService $readLog,
        LoggerInterface $logger
    ) {
        $this->esLogger = $elasticLogger;
        $this->readLog = $readLog;
        $this->logger = $logger;
    }

    /**
     * @param Notification $notification
     *
     * @throws \LogicException
     */
    public function logIncomingNotification(Notification $notification)
    {
        $log = NotificationLog::createFromNotification($notification);
        $log->setIncoming(true);
        $log->setStatus(NotificationStatus::STATUS_SENT);

        $this->esLogger->writeIntoAsync('NotificationLog', $log);
    }

    /**
     * @param Notification $notification
     *
     * @throws \LogicException
     */
    public function logOutcomingNotification(Notification $notification)
    {
        $log = NotificationLog::createFromNotification($notification);
        $log->setIncoming(false);
        $log->setStatus(NotificationLog::STATUS_SENT);

        $this->esLogger->writeIntoAsync('NotificationLog', $log);
    }

    /**
     * Set status of the notifications.
     *
     * @param NotificationStatus[] $statuses
     */
    public function setNotificationStatuses(array $statuses)
    {
        foreach ($statuses as $status) {
            $elasticId = $this->getElasticId($status->getNotificationId());

            $this->esLogger->update(
                NotificationLog::TYPE,
                $elasticId,
                ['status', 'statusMessage', 'extra'],
                [$status->getStatus(), $status->getMessage(), json_encode($status->getExtra())]
            );
        }
    }

    /**
     * @param string $notificationId
     *
     * @return array|null
     */
    protected function getElasticId(string $notificationId)
    {
        $query['query']['bool']['must'][] = ['match' => ['uid' => $notificationId]];

        $result = $this->getResult(NotificationLog::TYPE, $query, 1);

        //return the first result or null
        $result = count($result) > 0 ? $result[0] : null;

        if ($result === null) {
            $this->logger->emergency('Trying to set notification status for notification with id:'
                .$notificationId.' but the notification does not exist');
        }
        /* @var NotificationLog $result */

        return $result->getId();
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
            $result = $this->readLog->getMatchingEntities(
                $type,
                $query,
                $count,
                [],
                [['createdAt' => ['order' => 'desc']]]
            );
        } catch (\Throwable $e) {
            $this->logger->error($e);

            return [];
        }

        return $result;
    }
}
