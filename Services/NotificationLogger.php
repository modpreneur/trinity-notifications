<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\ElasticLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\ElasticReadLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;

/**
 * Class NotificationLogger.
 */
class NotificationLogger implements NotificationLoggerInterface
{
    /** @var ElasticLogServiceInterface */
    protected $esLogger;

    /** @var ElasticReadLogServiceInterface */
    protected $readLog;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * MessageLogger constructor.
     *
     * @param ElasticLogServiceInterface     $elasticLogger
     * @param ElasticReadLogServiceInterface $readLog
     * @param LoggerInterface                $logger
     */
    public function __construct(
        ElasticLogServiceInterface $elasticLogger,
        ElasticReadLogServiceInterface $readLog,
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


}
