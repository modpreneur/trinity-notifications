<?php
/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Services\NotificationManager;



/**
 * Class EntityListener
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


    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  Object */
    protected $entity;

    /** @var  ContainerInterface */
    protected $containerInterface;

    /** @var  EntityManager */
    protected $em;

    /** @var  NotificationManager */
    protected $notificationSender;

    /** @var  NotificationUtils */
    protected $processor;

    /** @var  Request */
    protected $request;



    /**
     * @param NotificationManager $notificationSender
     * @param NotificationUtils $annotationProcessor
     */
    function __construct(
        NotificationManager $notificationSender,
        NotificationUtils $annotationProcessor
    ) {
        $this->notificationSender = $notificationSender;
        $this->processor = $annotationProcessor;
    }



    /**
     * @param RequestStack $request_stack
     */
    public function setRequestStack(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }



    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Def in service.yml
     *
     * @param LifecycleEventArgs $args
     *
     * @return bool
     * @throws \Exception
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->em = $args->getEntityManager();
        $enable = $this->isNotificationEnabledForController();

        if ($enable) {
            return $this->sendNotification($args->getEntityManager(), $args->getObject(), self::PUT);
        }

        return FALSE;
    }



    /**
     * Def in service.yml
     *
     * @param LifecycleEventArgs $args
     *
     * @return bool
     * @throws \Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->em = $args->getEntityManager();

        return $this->sendNotification($args->getEntityManager(), $args->getObject(), self::POST);
    }



    /**
     * Def in service.yml
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->em = $args->getEntityManager();
        $this->entity = $args->getObject();
    }



    /**
     * Def in service.yml
     *
     * @param PreFlushEventArgs $args
     * @return bool|NULL
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $entity = $this->entity;
        $this->entity = NULL;

        if ($entity) {
            return $this->sendNotification($args->getEntityManager(), $entity, self::DELETE);
        }
    }



    /**
     * @param EntityManager $em
     * @param $entity
     * @param $method
     *
     * @return bool
     */
    private function sendNotification(EntityManager $em, $entity, $method)
    {
        if (!$this->processor->isNotificationEntity($entity)) {
            return FALSE;
        }

        $uow = $em->getUnitOfWork();
        $list = [];
        $doSendNotification = FALSE;

        if ( $uow ) {
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($entity);

            foreach ($changeset as $index => $value) {
                if ($this->processor->hasSource($entity, $index)) {
                    $list[] = $index;
                }
            }
            $doSendNotification = ( count($list) > 0 );
        } else {
            $doSendNotification = TRUE;
        }

        if ( $this->processor->hasHTTPMethod($entity, $method) && ($doSendNotification) || $method === "DELETE" ) {
            return $this->notificationSender->send($entity, $method);
        }

        return FALSE;
    }



    /**
     * @param bool $default (if request not set)
     * @return bool
     */
    private function isNotificationEnabledForController($default = TRUE)
    {
        if ($this->request) {
            $_controller = $this->request->get('_controller');
            $split = explode('::', $_controller);

            $controller = $split[0];
            $action = $split[1];

            return !$this->processor->isControllerOrActionDisabled($controller, $action);
        }

        return $default;
    }

}

