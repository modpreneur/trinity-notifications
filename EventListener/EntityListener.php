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
use Trinity\NotificationBundle\Event\DisableNotificationEvent;
use Trinity\NotificationBundle\Event\EnableNotificationEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\RepositoryInterfaceNotImplementedException;
use Trinity\NotificationBundle\Exception\SourceException;
use Trinity\NotificationBundle\Interfaces\NotificationEntityRepositoryInterface;
use Trinity\NotificationBundle\Notification\NotificationUtils;

/**
 * Class EntityListener.
 *
 * Function name - config.
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

    /** @var  Object */
    protected $entity;

    /** @var  NotificationUtils */
    protected $processor;

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

    /** @var  array */
    protected $entityDeletions = [];


    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationUtils        $annotationProcessor
     * @param bool                     $isClient
     *
     * @internal param NotificationManager $notificationManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        NotificationUtils $annotationProcessor,
        bool $isClient
    ) {
        $this->processor = $annotationProcessor;
        $this->isClient = $isClient;
        $this->eventDispatcher = $eventDispatcher;
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


    /**
     * Disable notification
     *
     * @param DisableNotificationEvent $event
     */
    public function disableNotification(DisableNotificationEvent $event)
    {
        $this->notificationEnabled = false;
    }


    /**
     * Enable notification
     *
     * @param EnableNotificationEvent $event
     */
    public function enableNotification(EnableNotificationEvent $event)
    {
        $this->notificationEnabled = true;
    }


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
                    'eagerLoaded' => $eagerLoadedEntity,
                    'entity' => $entity
                ];
            } else {
                throw new RepositoryInterfaceNotImplementedException(
                    'The repository of the entity ' . get_class($entity)
                    . ' must implement ' . NotificationEntityRepositoryInterface::class
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

        if ($enable && $this->notificationEnabled) {
            $this->sendNotification($args->getEntityManager(), $entity, self::POST);
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
    private function sendNotification(EntityManager $entityManager, $entity, string $method, array $options = [])
    {
        if (!$this->processor->isNotificationEntity($entity)) {
            return;
        }

        if ($this->processor->hasHTTPMethod($entity, strtolower($method))) {
            $this->eventDispatcher->dispatch(
                Events::SEND_NOTIFICATION,
                new SendNotificationEvent($entity, $method, $options)
            );

        }
    }


    /**
     * @param bool $default (if request not set)
     *
     * @return bool
     */
    private function isNotificationEnabledForController(bool $default = true) : bool
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

            return !$this->processor->isControllerOrActionDisabled($controller, $action);
        }

        return $default;
    }
}
