<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\NotificationManager;


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

    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  Object */
    protected $entity;

    /** @var  ContainerInterface */
    protected $containerInterface;

    /** @var  EntityManager */
    protected $entityManager;

    /** @var  NotificationManager */
    protected $notificationManager;

    /** @var  NotificationUtils */
    protected $processor;

    /** @var  Request */
    protected $request;

    /** @var  bool Is the current application client? */
    protected $isClient;


    /**
     * @param NotificationManager $notificationManager
     * @param NotificationUtils $annotationProcessor
     * @param bool $isClient
     */
    public function __construct(
        NotificationManager $notificationManager,
        NotificationUtils $annotationProcessor,
        $isClient
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
     * Def in service.yml.
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @return array
     *
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $enable = $this->isNotificationEnabledForController();


        if($enable){
            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                return $this->sendNotification($em, $entity, self::POST);
            }

            foreach ($uow->getScheduledEntityUpdates() as $entity) {
                return $this->sendNotification($em, $entity, self::PUT);
            }

            foreach ($uow->getScheduledEntityDeletions() as $entity) {
                return $this->sendNotification($em, $entity, self::DELETE);
            }
        }
    }


    /**
     * @param EntityManager $entityManager
     * @param $entity
     * @param $method
     *
     * @return bool|array
     */
    private function sendNotification(EntityManager $entityManager, $entity, $method)
    {
        $this->notificationManager->setEntityManager($this->entityManager);

        if (!$this->processor->isNotificationEntity($entity)) {
            return false;
        }

        $uow = $entityManager->getUnitOfWork();
        $list = [];

        if ($uow) {
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($entity);

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

        if ($this->processor->hasHTTPMethod($entity, $method) && ($doSendNotification) || $method === 'DELETE') {
            return $this->notificationManager->send($entity, $method, !$this->isClient);
        }

        return false;
    }


    /**
     * @param bool $default (if request not set)
     *
     * @return bool
     */
    private function isNotificationEnabledForController($default = true)
    {
        //for testing...
        if ($this->defaultValueForEnabledController !== null) {
            $default = $this->defaultValueForEnabledController;
        }

        if ($this->request) {
            $_controller = $this->request->get('_controller');
            $split = explode('::', $_controller);

            // No controller.
            if (count($split) != 2) {
                return true;
            }

            $controller = $split[0];
            $action = $split[1];

            return !$this->processor->isControllerOrActionDisabled($controller, $action);
        }

        return $default;
    }
}
