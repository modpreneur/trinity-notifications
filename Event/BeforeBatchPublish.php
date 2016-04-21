<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 09.04.16
 * Time: 11:35
 */

namespace Trinity\NotificationBundle\Event;


use Symfony\Component\EventDispatcher\Event;
use Trinity\NotificationBundle\Entity\NotificationBatch;


/**
 * Class BeforeBatchPublish
 */
class BeforeBatchPublish extends NotificationEvent
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