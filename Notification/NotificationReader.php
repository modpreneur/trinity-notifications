<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54
 */

namespace Trinity\NotificationBundle\Notification;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Event\AfterBatchProcessedEvent;
use Trinity\NotificationBundle\Event\BatchValidatedEvent;
use Trinity\NotificationBundle\Event\BeforeMessageReadEvent;
use Trinity\NotificationBundle\Event\Events;

class NotificationReader
{
    /**
     * @var string
     */
    protected $clientSecret;


    /**
     * @var NotificationParser
     */
    protected $parser;


    /**
     * @var  EventDispatcherInterface
     */
    protected $eventDispatcher;


    /**
     * @var array Indexed array of entities' aliases and real class names.
     * format:
     * [
     *    "user" => "App\Entity\User,
     *    "product" => "App\Entity\Product,
     *    ....
     * ]
     */
    protected $entities;


    /**
     * NotificationReader constructor.
     *
     * @param NotificationParser $parser
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $entities
     * @param string $clientSecret
     */
    public function __construct(NotificationParser $parser, EventDispatcherInterface $eventDispatcher, string $entities, string $clientSecret = null)
    {
        $this->parser = $parser;
        $this->eventDispatcher = $eventDispatcher;
        $this->clientSecret = $clientSecret;
        $this->entities = json_decode($entities, true);

        // Replace "_" for "-" in all keys
        foreach ($this->entities as $key => $className) {
            $newKey = str_replace("_", "-", $key);
            unset($this->entities[$key]);
            $this->entities[$newKey] = $className;
        }
    }


    /**
     * @param string $message
     *
     * @throws \Exception
     */
    public function read(string $message)
    {
        // If there are listeners for this event, fire it and get the message from it(it allows changing the message)
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_MESSAGE_READ)) {
            $beforeReadEvent = new BeforeMessageReadEvent($message);
            $beforeReadEvent->setMessage($message);
            /** @var BeforeMessageReadEvent $beforeReadEvent */
            $beforeReadEvent = $this->eventDispatcher->dispatch(Events::BEFORE_MESSAGE_READ, $beforeReadEvent);
            $message = $beforeReadEvent->getMessage();
        }

        $batch = new NotificationBatch();
        $batch->unpackBatch($message);

        $batch->setClientSecret($this->getClientSecret($batch));

        if (!$batch->isHashValid()) {
            throw new \Exception("Hash does not match");
        }

        // If there are listeners for this event, fire it and get the message from it(it allows changing the batch)
        if ($this->eventDispatcher->hasListeners(Events::BATCH_VALIDATED)) {
            $batchValidatedEvent = new BatchValidatedEvent($batch);
            /** @var BatchValidatedEvent $batchValidatedEvent */
            $batchValidatedEvent = $this->eventDispatcher->dispatch(Events::BATCH_VALIDATED, $batchValidatedEvent);
            $batch = $batchValidatedEvent->getBatch();
        }

        /** @var Notification $notification */
        foreach ($batch->getNotifications() as $notification) {
            $entityName = $notification->getData()["entityName"];

            if (!array_key_exists($entityName, $this->entities)) {
                throw new \Exception("No classname found for entityName: \"" . $entityName . "\". 
                Have you defined it in the configuration under trinity_notification:entities?"
                );
            }

            $this->parser->parseNotification($notification->getData(), $this->entities[$entityName], $notification->getMethod());
        }

        // If there are listeners for this event, fire it
        if ($this->eventDispatcher->hasListeners(Events::AFTER_BATCH_PROCESSED)) {
            $batchValidatedEvent = new AfterBatchProcessedEvent($batch);
            /** @var BatchValidatedEvent $batchValidatedEvent */
            $this->eventDispatcher->dispatch(Events::AFTER_BATCH_PROCESSED, $batchValidatedEvent);
        }
    }


    /**
     * Get client secret from the batch.
     *
     * Override this method to customize the behaviour.
     *
     * @param NotificationBatch $batch
     *
     * @return string
     */
    public function getClientSecret(NotificationBatch $batch)
    {
        return $this->clientSecret;
    }


}