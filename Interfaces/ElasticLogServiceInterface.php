<?php

namespace Trinity\NotificationBundle\Interfaces;

/**
 * Interface based on trinity/logger service
 *
 * Class ElasticLogService.
 */
interface ElasticLogServiceInterface
{
    /**
     * @param string $typeName //log name
     * @param $entity //entity
     *
     * @return void
     */
    public function writeIntoAsync($typeName, $entity);

    /**
     * @param string $typeName
     * @param string $id
     * @param array $types
     * @param array $values
     */
    public function update($typeName, $id, array $types, array $values);
}