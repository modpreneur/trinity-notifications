<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 17:06
 */

namespace Trinity\NotificationBundle\EventListener;

use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Trinity\NotificationBundle\Notification\NotificationManager;

/**
 * Class KernelTerminateListener
 * @package Trinity\NotificationBundle\EventListener
 */
class KernelTerminateListener
{
    /** @var  NotificationManager */
    protected $notificationManager;

    /**
     * Logger is send here directly. Interface does not contain
     * methods that our exception flow require(method addError in monolog/logger and following)
     * Exchange with different implementation of LoggerInterface would bring incorrect
     * behavior.
     *
     * @var Logger
     */
    protected $logger;



    /**
     * KernelTerminateListener constructor.
     * @param NotificationManager $notificationManager
     * @param Logger $logger
     */
    public function __construct(NotificationManager $notificationManager, Logger $logger)
    {
        $this->notificationManager = $notificationManager;
        $this->logger = $logger;
    }


    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        try {
            //send batch only on successful requests
            if ($event->getResponse()->getStatusCode() < 400) {
                $this->notificationManager->sendBatch();
            }
            //catch exceptions and php7 errors
        } catch (\Throwable $e) {
            /*
             * It may show multiple times store multiple logs when exception is thrown,
             * because of api calls (each call store same exception)
             */
            $this->logger->addError($e);
        }
    }
}