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
    use Trinity\NotificationBundle\Notification\Annotations\NotificationProcessor;
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

        /** @var  NotificationProcessor */
        protected $processor;

        /** @var  Request */
        protected $request;



        function __construct(
            NotificationManager $notificationSender,
            NotificationProcessor $annotationProcessor
        ) {
            $this->notificationSender = $notificationSender;
            $this->processor = $annotationProcessor;
        }



        public function setRequest(RequestStack $request_stack)
        {
            $this->request = $request_stack->getCurrentRequest();
        }



        /**
         * Def in service.yml
         *
         * @param LifecycleEventArgs $args
         *
         * @throws \Exception
         */
        public function postUpdate(LifecycleEventArgs $args)
        {
            $this->em = $args->getEntityManager();

            $entity = $args->getObject();
            // @todo trait type
            $traits = class_uses($entity);

            $enable = $this->isNotificationEnable();
            if ($enable) {
                $this->sendNotification($args->getEntityManager(), $args->getObject(), self::PUT);
            }
        }



        /**
         * Def in service.yml
         *
         * @param LifecycleEventArgs $args
         *
         * @throws \Exception
         */
        public function postPersist(LifecycleEventArgs $args)
        {
            $this->em = $args->getEntityManager();
            $this->sendNotification($args->getEntityManager(), $args->getObject(), self::POST);
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
         */
        public function preFlush(PreFlushEventArgs $args)
        {
            $entity = $this->entity;
            $this->entity = null;

            if ($entity) {
                $this->sendNotification($args->getEntityManager(), $entity, self::DELETE);
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
                return;
            }

            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($entity);
            $list = [];

            foreach ($changeset as $index => $value) {
                if ($this->processor->hasSource($entity, $index)) {
                    $list[] = $index;
                }
            }

            if ($this->processor->isMethodEnabled($entity, $method) && ((count($list) > 0) || $method === "DELETE")) {
                $this->notificationSender->send($entity, $method);
            }
        }



        /**
         * @return bool
         */
        private function isNotificationEnable()
        {

            if ($this->request) {
                $_controller = $this->request->get('_controller');
                $split = explode('::', $_controller);

                $controller = $split[0];
                $action = $split[1];

                return !$this->processor->isControllerOrActionDisabled($controller, $action);
            }

            return true;
        }

    }

