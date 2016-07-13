<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 18:20
 */

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\NotificationBatch;

/**
 * Class ChangesDoneEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class ChangesDoneEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.changesDone';
    
    /** @var array */
    protected $entities;

    /** @var  NotificationBatch */
    protected $batch;

    /**
     * ChangesDoneEvent constructor.
     *
     * @param array             $entities
     * @param NotificationBatch $batch
     */
    public function __construct(array $entities, NotificationBatch $batch)
    {
        $this->entities = $entities;
        $this->batch = $batch;
    }

    /**
     * @return array
     */
    public function getEntities() : array
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return NotificationBatch
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param NotificationBatch $batch
     */
    public function setBatch($batch)
    {
        $this->batch = $batch;
    }
}
