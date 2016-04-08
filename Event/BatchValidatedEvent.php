<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:52
 */

namespace Trinity\NotificationBundle\Event;
use Trinity\NotificationBundle\Entity\NotificationBatch;


/**
 * Class BatchValidatedEvent
 */
class BatchValidatedEvent extends NotificationEvent
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