<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 14.04.16
 * Time: 8:44
 */

namespace Trinity\NotificationBundle\EventListener;


use Doctrine\ORM\EntityManagerInterface;
use Trinity\NotificationBundle\Event\AfterBatchProcessedEvent;
use Trinity\NotificationBundle\Event\BatchValidatedEvent;

class NotificationEventsListener
{

    /** @var  EntityManagerInterface */
    protected $entityManager;


    /**
     * NotificationEventsListener constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Listen to a BatchValidatedEvent. When the event is fired, begins a database transaction.
     * This allows rollback the changes if one of the notifications edit fail.
     * The transaction must be commited on AfterBatchProcessedEvent.
     *
     * @param BatchValidatedEvent $event
     */
    public function onBatchValidated(BatchValidatedEvent $event)
    {
        $this->entityManager->getConnection()->beginTransaction();
    }


    /**
     * Listen to a AfterBatchProcessedEvent. When the event is fired, commit the database transaction.
     *
     * @param AfterBatchProcessedEvent $event
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function onAfterBatchProcessed(AfterBatchProcessedEvent $event)
    {
        $this->entityManager->getConnection()->commit();
    }
}