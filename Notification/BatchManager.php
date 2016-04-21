<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 16:48
 */

namespace Trinity\NotificationBundle\Notification;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\Bundle\BunnyBundle\Producer\Producer;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Event\BeforeBatchPublish;
use Trinity\NotificationBundle\Event\Events;

class BatchManager
{
    /** @var NotificationBatch[] */
    protected $batches = [];


    /** @var Producer */
    protected $producer;


    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;


    /**
     * BatchManager constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @return Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }


    /**
     * This method is called in driver.
     * 
     * @param Producer $producer
     */
    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;
    }


    /**
     * Add notification to the batch. Create a new batch if it does not exist.
     *
     * @param string $clientId
     * @param array $notifications
     *
     * @return NotificationBatch Created batch or batch which was added the data.
     */
    public function createBatch(string $clientId, array $notifications = [])
    {
        $returnBatch = null;

        foreach ($this->batches as $batch) {
            if ($batch->getClientId() == $clientId) {
                $returnBatch = $batch;
                break;
            }
        }

        if ($returnBatch) {
            $returnBatch->addNotifications($notifications);
        } else {
            $returnBatch = new NotificationBatch();
            $returnBatch->addNotifications($notifications);
            $returnBatch->setClientId($clientId);

            $this->batches[] = $returnBatch;
        }

        return $returnBatch;
    }


    /**
     * Send all batches to the rabbit.
     */
    public function send()
    {
        $hasListeners = $this->eventDispatcher->hasListeners(Events::BEFORE_BATCH_PUBLISH);

        foreach ($this->batches as $batch) {
            if ($hasListeners) {
                $beforeBatchPublish = new BeforeBatchPublish($batch);
                /** @var BeforeBatchPublish $beforeBatchPublish */
                $beforeBatchPublish = $this->eventDispatcher->dispatch(Events::BEFORE_BATCH_PUBLISH, $beforeBatchPublish);
                $batch = $beforeBatchPublish->getBatch();
            }

            $this->producer->publish($batch->packBatch(), $batch->getClientId());
        }
    }


    /**
     * @return NotificationBatch[]
     */
    public function getBatches()
    {
        return $this->batches;
    }


    /**
     * @param NotificationBatch[] $batches
     * @return BatchManager
     */
    public function setBatches(array $batches)
    {
        $this->batches = $batches;

        return $this;
    }
}
