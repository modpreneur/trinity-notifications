<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\RepositoryInterfaceNotImplementedException;
use Trinity\NotificationBundle\Exception\SourceException;
use Trinity\NotificationBundle\Interfaces\NotificationEntityRepositoryInterface;
use Trinity\NotificationBundle\Notification\AnnotationsUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\Notification\NotificationUtils;

/**
 * Class EntityListener.
 *
 * @author Tomáš Jančar
 */
class EntityListener
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    /** @var  bool */
    protected $defaultValueForEnabledController;

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  Request */
    protected $request;

    /** @var  bool Is the current application client? */
    protected $isClient;

    /** @var NotificationEntityInterface */
    protected $currentProcessEntity;

    /** @var bool is listening enabled for this listener */
    protected $notificationEnabled = true;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  AnnotationsUtils */
    protected $annotationsUtils;

    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  array */
    protected $entityDeletions = [];

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationUtils        $annotationProcessor
     * @param AnnotationsUtils         $annotationsUtils
     * @param EntityConverter          $entityConverter
     * @param bool                     $isClient
     *
     * @internal param NotificationManager $notificationManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        NotificationUtils $annotationProcessor,
        AnnotationsUtils $annotationsUtils,
        EntityConverter $entityConverter,
        $isClient
    ) {
        $this->notificationUtils = $annotationProcessor;
        $this->eventDispatcher = $eventDispatcher;
        $this->annotationsUtils = $annotationsUtils;
        $this->entityConverter = $entityConverter;
        $this->isClient = $isClient;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

//
//    /**
//     * Disable notification
//     *
//     * @param DisableNotificationEvent $event
//     */
//    public function disableNotification(DisableNotificationEvent $event)
//    {
//        $this->notificationEnabled = false;
//    }
//
//
//    /**
//     * Enable notification
//     *
//     * @param EnableNotificationEvent $event
//     */
//    public function enableNotification(EnableNotificationEvent $event)
//    {
//        $this->notificationEnabled = true;
//    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Trinity\NotificationBundle\Exception\RepositoryInterfaceNotImplementedException
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $repository = $eventArgs->getEntityManager()->getRepository(get_class($entity));

        if ($entity instanceof NotificationEntityInterface) {
            if ($repository instanceof NotificationEntityRepositoryInterface) {
                $eagerLoadedEntity = $repository->findEagerly($entity->getId());
                $this->entityDeletions[] = [
                    // The clone has be be there!
                    // Without it the object is still bounded to entity manager which removes it's id.
                    'eagerLoaded' => clone $eagerLoadedEntity,
                    'entity' => $entity,
                ];
            } else {
                throw new RepositoryInterfaceNotImplementedException(
                    'The repository of the entity '.get_class($entity)
                    .' must implement '.NotificationEntityRepositoryInterface::class
                );
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws SourceException
     * @throws NotificationException
     */
    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $postRemoveEntity = $eventArgs->getEntity();

        foreach ($this->entityDeletions as $item) {
            $preRemoveEntity = $item['entity'];

            if (!($preRemoveEntity instanceof NotificationEntityInterface)) {
                continue;
            }

            // if the entity which was added in preRemove is the same entity as the entity from postRemove
            // the database delete query was successful and we can send a notification
            // because the $post(pre)removeEntity does not have an id we use eager loaded entity which has the id
            if ($postRemoveEntity === $preRemoveEntity) {
                $this->sendNotification($eventArgs->getEntityManager(), $item['eagerLoaded'], self::DELETE);
            }
        }
    }

    /**
     * Def in service.yml.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws SourceException
     * @throws NotificationException
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $enable = $this->isNotificationEnabledForController();
        $entity = $args->getObject();

        if ($enable && $this->notificationEnabled) {
            $this->sendNotification($args->getEntityManager(), $entity, self::PUT);
        }
    }

    /**
     * Def in service.yml.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $enable = $this->isNotificationEnabledForController();
        $entity = $args->getObject();
        $entityManager = $args->getEntityManager();

        if ($enable && $this->notificationEnabled) {
            $this->sendNotification($entityManager, $entity, self::POST);
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param               $entity
     * @param string        $method
     * @param array         $options
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    protected function sendNotification(EntityManager $entityManager, $entity, $method, array $options = [])
    {
        if (!$this->notificationUtils->isNotificationEntity($entity)) {
            return;
        }

        if ($this->notificationUtils->hasHTTPMethod($entity, strtolower($method))) {
            $changeSet = $this->processChangeset($entityManager->getUnitOfWork()->getEntityChangeSet($entity));

            /** @var \Trinity\NotificationBundle\Annotations\Source $entityDataSource */
            $entityDataSource = $this->annotationsUtils->getClassSourceAnnotation($entity);
            $columns = array_flip($entityDataSource->getColumns());

            $columnsToNotify = array_keys(array_intersect_key($columns, $changeSet));

            if (count($columnsToNotify) > 0 || strtolower($method) === 'delete') {
                $this->eventDispatcher->dispatch(
                    SendNotificationEvent::NAME,
                    new SendNotificationEvent($entity, $changeSet, $method, $options, false)
                );
            }
        }
    }

    /**
     * Convert objects in changeSet to scalars.
     *
     * @param array $changeSet
     *
     * @return array
     */
    protected function processChangeset(array $changeSet)
    {
        foreach ($changeSet as $propertyName => $propertyChangeset) {
            //0 = old value; 1 = new value
            $changeSet[$propertyName][0] = $this->entityConverter->convertToString($propertyChangeset[0]);
            $changeSet[$propertyName][1] = $this->entityConverter->convertToString($propertyChangeset[1]);
        }

        return $changeSet;
    }

    /**
     * @param bool $default (if request not set)
     *
     * @return bool
     */
    protected function isNotificationEnabledForController( $default = true)
    {
        //for testing...
        if ($this->defaultValueForEnabledController !== null) {
            $default = $this->defaultValueForEnabledController;
        }

        if ($this->request) {
            $_controller = $this->request->get('_controller');
            $split = explode('::', $_controller);

            // No controller.
            if (count($split) !== 2) {
                return true;
            }

            list($controller, $action) = $split;

            return !$this->notificationUtils->isControllerOrActionDisabled($controller, $action);
        }

        return $default;
    }
}
