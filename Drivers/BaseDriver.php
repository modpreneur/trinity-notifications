<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\NotificationUtils;
use Trinity\NotificationBundle\Notification\BatchManager;
use Trinity\NotificationBundle\Notification\EntityConverter;


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

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /**
     * @var array Array which contains already processed entities(should fix deleting errors).
     *
     * First level indexes are classnames.
     * Second level indexes are entity ids.
     */
    protected $notifiedEntities = [];


    /**
     * @var BatchManager
     */
    protected $batchManager;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param TokenStorageInterface $tokenStorage
     * @param BatchManager $batchManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        TokenStorageInterface $tokenStorage = null,
        BatchManager $batchManager
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->tokenStorage = $tokenStorage;
        $this->batchManager = $batchManager;
    }


    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Returns object encoded in json.
     * Encode only first level (FK are expressed as ID strings).
     *
     * @param array $entity
     * @param string $secret
     *
     * @param array $extraFields
     *
     * @return string
     * @throws \Exception
     */
    protected function JSONEncodeObject($entity, $secret, $extraFields = [])
    {
        foreach ($extraFields as $extraFieldKey => $extraFieldValue) {
            $entity[$extraFieldKey] = $extraFieldValue;
        }

        $entity['timestamp'] = (new \DateTime())->getTimestamp();
        $entity['hash'] = hash('sha256', $secret . (implode(',', $entity)));

        // error fix...
        // todo: it is necessary to distinguish null and empty string
        $entity = str_replace('null', '""', $entity);

        return json_encode($entity);
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
    protected function isEntityAlreadyProcessed(NotificationEntityInterface $entity)
    {
        $class = get_class($entity);

        return array_key_exists($class, $this->notifiedEntities) && in_array($entity->getId(), $this->notifiedEntities[$class]);
    }
}

