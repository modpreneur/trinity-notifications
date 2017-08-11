<?php

namespace Trinity\NotificationBundle\Services;

use Psr\Log\LoggerInterface;
use Trinity\Bundle\MessagesBundle\Message\StatusMessage;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationLog;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\ElasticLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\ElasticReadLogServiceInterface;

/**
 * Class NotificationStatusManager.
 */
class NotificationStatusManager extends AbstractNotificationStatusManager
{
    /** @var  NotificationLog */
    protected $lastNotificationLog;

    /** @var  EntityAliasTranslator */
    protected $aliasTranslator;

    /**
     * NotificationStatusManager constructor.
     *
     * @param ElasticReadLogServiceInterface    $elasticReader
     * @param ElasticLogServiceInterface        $elasticWriter
     * @param LoggerInterface          $logger
     * @param EntityAliasTranslator    $aliasTranslator
     */
    public function __construct(
        ElasticReadLogServiceInterface $elasticReader,
        ElasticLogServiceInterface $elasticWriter,
        LoggerInterface $logger,
        EntityAliasTranslator $aliasTranslator
    ) {
        parent::__construct($elasticReader, $elasticWriter, $logger);

        $this->aliasTranslator = $aliasTranslator;
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
        //get message, which parent is the message, which was the notification in and it's type is 'status'
        $query['query']['bool']['must'][] = ['match' => ['parentMessageUid' => $notification->getMessageId()]];
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
            $query['query']['bool']['must'][] = ['match' => ['uid' => $notification->getUid()]];
            $result = $this->getResult(NotificationLog::TYPE, $query, 1);

            if (count($result) === 0) {
                // TODO @JakubFajkus some specific Exception
                throw new \Exception('No NotificationLog with id:'.$notification->getUid().' found!');
            }

            $this->lastNotificationLog = $result[0];
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
        $query['query']['bool']['must'][] = ['match' => ['entityName' => $entityName]];
        $query['query']['bool']['must'][] = ['match' => ['entityId' => $entity->getId()]];
        $query['query']['bool']['must'][] = ['match' => ['clientId' => $clientId]];

        $result = $this->getResult(NotificationLog::TYPE, $query, 1);

        //return the first result or null
        return count($result) > 0 ? $result[0] : null;
    }

    /**
     * @param string $type
     * @param array  $query
     * @param int    $count
     *
     * @return array
     */
    public function getResult(string $type, array $query, int $count)
    {
        try {
            $result = $this->elasticReader->getMatchingEntities(
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
