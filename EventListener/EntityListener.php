<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\NotificationManager;
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

    /** @var  NotificationManager */
    protected $notificationManager;

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


    /**
     * @param NotificationManager $notificationManager
     * @param NotificationUtils   $annotationProcessor
     * @param bool                $isClient
     */
    public function __construct(
        NotificationManager $notificationManager,
        NotificationUtils $annotationProcessor,
        bool $isClient
    ) {
        $this->notificationManager = $notificationManager;
        $this->processor = $annotationProcessor;
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
     *
     * @return array
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
     *
     * @return bool
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
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $enable = $this->isNotificationEnabledForController();

        if ($enable && $this->notificationEnabled) {
            foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
                $this->sendNotification($entityManager, $entity, self::DELETE);
            }
        }
    }


    /**
     * @param EntityManager $entityManager
     * @param               $entity
     * @param string        $method
     *
     * @param array         $options
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    private function sendNotification(EntityManager $entityManager, $entity, string $method, array $options = [])
    {
        $this->notificationManager->setEntityManager($entityManager);

        if (!$this->processor->isNotificationEntity($entity)) {
            return;
        }

        $unitOfWork = $entityManager->getUnitOfWork();
        $list = [];

        if ($unitOfWork) {
            $unitOfWork->computeChangeSets();
            $changeset = $unitOfWork->getEntityChangeSet($entity);

            foreach ($changeset as $index => $value) {
                if ($this->processor->hasSource($entity, $index) || $this->processor->hasDependedSource(
                    $entity,
                    $index
                    )
                ) {
                    $list[] = $index;
                }
            }

            $doSendNotification = (count($list) > 0);
        } else {
            $doSendNotification = true;
        }

        if ($this->processor->hasHTTPMethod($entity, strtolower($method)) && ($doSendNotification ||
                strtoupper($method) === self::POST)
        ) {
            $this->notificationManager->queueEntity($entity, $method, !$this->isClient, $options);
        }
    }


    /**
     * @param bool $default (if request not set)
     *
     * @return bool
     */
    private function isNotificationEnabledForController(bool $default = true)
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
