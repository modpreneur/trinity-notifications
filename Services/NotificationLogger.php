<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;
use Trinity\NotificationBundle\Interfaces\NotificationLogStorageInterface;

/**
 * Class NotificationLogger.
 */
class NotificationLogger implements NotificationLoggerInterface
{
    /** @var  LoggerInterface */
    protected $logger;

    /** @var NotificationLogStorageInterface */
    protected $logStorage;

    /**
     * MessageLogger constructor.
     *
     * @param LoggerInterface $logger
     * @param NotificationLogStorageInterface $logStorage
     */
    public function __construct(LoggerInterface $logger, NotificationLogStorageInterface $logStorage)
    {
        $this->logger = $logger;
        $this->logStorage = $logStorage;
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

        $this->logStorage->logNotification($log);
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

        $this->logStorage->logNotification($log);
    }

    /**
     * Set status of the notifications.
     *
     * @param NotificationStatus[] $statuses
     */
    public function setNotificationStatuses(array $statuses)
    {
        $this->logStorage->updateNotificationStatuses($statuses);
    }

//    /**
//     * @param string $type
//     * @param array  $query
//     * @param int    $count
//     *
//     * @return array
//     */
//    protected function getResult(string $type, array $query, int $count)
//    {
//        try {
//            $result = $this->readLog->getMatchingEntities(
//                $type,
//                $query,
//                $count,
//                [],
//                [['createdAt' => ['order' => 'desc']]]
//            );
//        } catch (\Throwable $e) {
//            $this->logger->error($e);
//
//            return [];
//        }
//
//        return $result;
//    }

}
