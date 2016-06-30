<?php

namespace Trinity\NotificationBundle\Services;

use Trinity\Bundle\LoggerBundle\Services\ElasticLogService;
use Trinity\Bundle\LoggerBundle\Services\ElasticReadLogService;
use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\EntityStatusLog;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class NotificationStatusManager
 *
 * @package Trinity\NotificationBundle\Services
 */
class NotificationStatusManager
{

    /** @var  ElasticReadLogService */
    protected $elasticReader;

    /** @var  ElasticLogService */
    protected $elasticWriter;

    /**
     * NotificationStatusManager constructor.
     *
     * @param ElasticReadLogService $elasticReader
     * @param ElasticLogService     $elasticWriter
     */
    public function __construct(ElasticReadLogService $elasticReader, ElasticLogService $elasticWriter)
    {
        $this->elasticReader = $elasticReader;
        $this->elasticWriter = $elasticWriter;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface|int         $client Instance of ClientInterface or client id
     * @param string                      $orderBy
     *
     * @return null|EntityStatusLog
     */
    public function getEntityStatus(NotificationEntityInterface $entity, $client, string $orderBy = 'changedAt')
    {
        $clientId = $this->getClientId($client);

        $query['query']['bool']['must'][] = ['match' => ['entityClass' => $this->getEntityClass($entity)]];
        $query['query']['bool']['must'][] = ['match' => ['entityId' => $entity->getId()]];
        $query['query']['bool']['must'][] = ['match' => ['clientId' => $clientId]];

        $result = $this->elasticReader->getMatchingEntities(
            EntityStatusLog::TYPE,
            $query,
            1,
            [],
            [[$orderBy => ['order' => 'desc']]]
        );

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface|int         $client
     * @param int                         $changedAt
     * @param string                      $messageUid
     * @param string                      $status
     */
    public function setEntityStatus(
        NotificationEntityInterface $entity,
        $client,
        int $changedAt,
        string $messageUid,
        string $status
    ) {
        //todo @GabrielBordovsky delete old status!

        $log = new EntityStatusLog();
        $log->setEntityId($entity->getId());
        $log->setEntityClass($this->getEntityClass($entity));
        $log->setClientId($this->getClientId($client));
        $log->setChangedAt($changedAt);
        $log->setMessageUid($messageUid);
        $log->setStatus($status);

        $this->elasticWriter->writeInto(EntityStatusLog::TYPE, $log);
    }


    /**
     * Get entity class
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
