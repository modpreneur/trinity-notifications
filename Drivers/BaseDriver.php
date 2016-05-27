<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\Notification\NotificationUtils;

/**
 * Class BaseDriver
 *
 * @package Trinity\NotificationBundle\Drivers
 */
abstract class BaseDriver implements NotificationDriverInterface
{
    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @var array Array which contains already processed entities(should fix deleting errors).
     *
     * First level indexes are classnames.
     * Second level indexes are entity ids.
     */
    protected $notifiedEntities = [];


    /** @var BatchManager */
    protected $batchManager;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter          $entityConverter
     * @param NotificationUtils        $notificationUtils
     * @param BatchManager             $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->batchManager = $batchManager;
    }


    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Add entity to notifiedEntities array.
     *
     * @param NotificationEntityInterface $entity
     */
    protected function addEntityToNotifiedEntities(NotificationEntityInterface $entity)
    {
        // add id to the entity array so it will not be notified again
        $this->notifiedEntities[get_class($entity)][] = $entity->getId();
    }


    /**
     * Check if the current entity was already processed.
     *
     * @param NotificationEntityInterface $entity
     *
     * @return bool
     */
    protected function isEntityAlreadyProcessed(NotificationEntityInterface $entity) : bool
    {
        $class = get_class($entity);

        return array_key_exists($class, $this->notifiedEntities) &&
            in_array($entity->getId(), $this->notifiedEntities[$class], false);
    }
}

