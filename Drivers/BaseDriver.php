<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;
use Trinity\NotificationBundle\Notification\AnnotationsUtils;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\Notification\NotificationUtils;

/**
 * Class BaseDriver.
 */
abstract class BaseDriver implements NotificationDriverInterface
{
    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  AnnotationsUtils */
    protected $annotationsUtils;

    /**
     * @var array Array which contains already processed entities(should fix deleting errors).
     *
     * First level indexes are classnames.
     * Second level indexes are entity ids.
     */
    protected $notifiedEntities = [];

    /** @var BatchManager */
    protected $batchManager;

    /** @var NotificationLoggerInterface */
    protected $notificationLogger;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param EntityConverter             $entityConverter
     * @param NotificationUtils           $notificationUtils
     * @param BatchManager                $batchManager
     * @param AnnotationsUtils            $annotationsUtils
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        BatchManager $batchManager,
        AnnotationsUtils $annotationsUtils,
        NotificationLoggerInterface $notificationLogger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->batchManager = $batchManager;
        $this->annotationsUtils = $annotationsUtils;
        $this->notificationLogger = $notificationLogger;
    }

    /**
     * Clears the inner state of the driver
     */
    public function clear()
    {
        $this->notifiedEntities = [];
    }

    /**
     * Add entity to notifiedEntities array.
     *
     * @param NotificationEntityInterface $entity
     * @param string                      $clientId
     */
    protected function addEntityToNotifiedEntities(NotificationEntityInterface $entity, string $clientId)
    {
        // add id to the entity array so it will not be notified again
        $this->notifiedEntities[$clientId][get_class($entity)][] = $entity->getId();
    }

    /**
     * Check if the current entity was already processed.
     *
     * @param NotificationEntityInterface $entity
     * @param string                      $clientId
     *
     * @return bool
     */
    protected function isEntityAlreadyProcessed(NotificationEntityInterface $entity, string $clientId) : bool
    {
        $class = get_class($entity);

        return array_key_exists($clientId, $this->notifiedEntities) && //if the client array exists
            array_key_exists($class, $this->notifiedEntities[$clientId]) && // and the class array exists
            // and the entity id exists in the class array
            in_array($entity->getId(), $this->notifiedEntities[$clientId][$class], false);
    }

    /**
     * Change indexes in changeSet. Change '0' to 'old' and '1' to 'new'.
     *
     * @param array $changeSet
     *
     * @return array
     */
    protected function changeIndexesInChangeSet(array $changeSet)
    {
        //change indexes 0,1 in changeSet to keys 'old' and 'new'
        foreach ($changeSet as $property => $item) {
            $changeSet[$property]['old'] = $item[0];
            /* @noinspection MultiAssignmentUsageInspection It is more readable this way*/
            $changeSet[$property]['new'] = $item[1];
            unset($changeSet[$property][0], $changeSet[$property][1]);
        }

        return $changeSet;
    }

    /**
     * @param array $entityArray
     * @param $notifiedProperties
     *
     * @return array
     */
    protected function removeNotNotifiedProperties(array $entityArray, $notifiedProperties)
    {
        return array_intersect_key($entityArray, $notifiedProperties);
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet
     *
     * @return array
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    protected function prepareChangeSet(NotificationEntityInterface $entity, array $changeSet)
    {
        //get all properties, which are in the @Source annotation
        $notifiedProperties = array_flip(
            $this->annotationsUtils->getClassSourceAnnotation($entity)->getColumns()
        );

        //remove properties, which should not be sent
        $changeSet = $this->removeNotNotifiedProperties($changeSet, $notifiedProperties);

        //change indexes 0,1 in changeSet to keys 'old' and 'new'
        return $this->changeIndexesInChangeSet($changeSet);
    }

    /**
     * @param Notification $notification
     */
    protected function logNotification(Notification $notification)
    {
        $this->notificationLogger->logOutcomingNotification($notification);
    }
}
