<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendNotificationEvent;
use Trinity\NotificationBundle\Exception\RepositoryInterfaceNotImplementedException;
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
     */
    public function disableNotification()
    {
        $this->notificationEnabled = false;
    }


    /**
     * Enable notification
     */
    public function enableNotification()
    {
        $this->notificationEnabled = true;
    }


    /**
     * Def in service.yml.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
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
     * Def in service.yml.
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\NotificationBundle\Exception\RepositoryInterfaceNotImplementedException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $enable = $this->isNotificationEnabledForController();

        if ($enable && $this->notificationEnabled) {
            foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
                //todo: queuing notification in this phase is wrong.
                //todo: If the deleting fail in the DB the notification will be still sent.

                if (!$entity instanceof NotificationEntityInterface) {
                    continue;
                }
                //Get the entity from the database EAGERly and clone it because in this phase it has the ID
                //later on the entity is deleted and the ID is removed from the object
                //but the cloned object remains untouched and the id is still there.
                //NOTE: eager loading does not make sense when creating entities.
                //      In that case the entity ID is not present(entity is not yet persisted) and thus it can not be retrieved from the DB.
                $repository = $eventArgs->getEntityManager()->getRepository(get_class($entity));

                if ($repository instanceof NotificationEntityRepositoryInterface
                ) {
                    $eagerLoadedEntity = $repository->findEagerly($entity->getId());
                    $this->sendNotification($entityManager, clone $eagerLoadedEntity, self::DELETE);
                } else {
                    throw new RepositoryInterfaceNotImplementedException(
                        'The repository of the entity ' . get_class($entity)
                        . ' must implement ' . NotificationEntityRepositoryInterface::class
                    );
                }
            }
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

        if ($this->processor->hasHTTPMethod($entity, strtolower($method)) &&
            (strtoupper($method) === self::POST || strtoupper($method) === self::DELETE)
        ) {
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
