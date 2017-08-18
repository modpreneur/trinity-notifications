<?php

namespace Trinity\NotificationBundle\Services;

use Trinity\NotificationBundle\Interfaces\ElasticLogServiceInterface;
use Trinity\NotificationBundle\Interfaces\ElasticReadLogServiceInterface;

class DummyElasticService implements ElasticReadLogServiceInterface, ElasticLogServiceInterface
{

    /**
     * @param string $typeName
     * @param string $id
     * @param array $types
     * @param array $values
     */
    public function update($typeName, $id, array $types, array $values)
    {

    }

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
    )
    {

    }

    /**
     * Get the entities by the same query syntax as it is for doctrine repositories.
     *
     * @param string $typeName Name of the elastic type
     * @param array $parameters e.g. ['name' => 'jack', 'age' => 25]
     *
     * @return array
     */
    public function getMatchingEntitiesSimple($typeName, array $parameters)
    {

    }

    /**
     * @param string $typeName //log name
     * @param $entity //entity
     *
     * @return void
     */
    public function writeIntoAsync($typeName, $entity)
    {

    }

    /**
     * @param string $typeName //log name
     * @param $entity //entity
     *
     * @return void
     */
    public function writeIntoSync($typeName, $entity)
    {

    }
}