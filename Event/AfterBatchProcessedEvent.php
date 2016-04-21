<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 14.04.16
 * Time: 8:42
 */

namespace Trinity\NotificationBundle\Event;


use Trinity\NotificationBundle\Entity\NotificationBatch;

/**
 * Class AfterBatchProcessedEvent
 */
class AfterBatchProcessedEvent extends NotificationEvent
{
    /** @var NotificationBatch */
    protected $batch;


    /**
     * BatchValidatedEvent constructor.
     * @param NotificationBatch $batch
     */
    public function __construct(NotificationBatch $batch)
    {
        $this->batch = $batch;
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