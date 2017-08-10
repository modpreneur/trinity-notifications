<?php

namespace Trinity\NotificationBundle\Interfaces;

/**
 * Interface based on trinity/logger service
 *
 * Class ElasticReadLogService
 * @package Trinity\Bundle\LoggerBundle\Services
 */
interface ElasticReadLogServiceInterface
{
    /**
     * @param string $typeName
     * @param array $searchParams
     * @param int $limit
     * @param array $select
     * @param array $order
     *
     * @return array
     */
    public function getMatchingEntities(
        $typeName,
        array $searchParams = [],
        $limit = 0,
        array $select = [],
        array $order = [['createdAt' => ['order' => 'desc']]]
    );

    /**
     * Get the entities by the same query syntax as it is for doctrine repositories.
     *
     * @param string $typeName Name of the elastic type
     * @param array $parameters e.g. ['name' => 'jack', 'age' => 25]
     *
     * @return array
     */
    public function getMatchingEntitiesSimple($typeName, array $parameters);

}