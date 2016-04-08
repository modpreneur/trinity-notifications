<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 17:06
 */

namespace Trinity\NotificationBundle\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Trinity\NotificationBundle\Notification\NotificationManager;

class KernelTerminateListener
{
    /** @var  NotificationManager */
    protected $notificationManager;


    /** @var  LoggerInterface */
    protected $logger;


    /** @var  bool */
    protected $debugMode;


    /**
     * KernelTerminateListener constructor.
     * @param NotificationManager $notificationManager
     * @param LoggerInterface $logger
     * @param bool $debugMode
     */
    public function __construct(NotificationManager $notificationManager, LoggerInterface $logger, bool $debugMode = false)
    {
        $this->notificationManager = $notificationManager;
        $this->logger = $logger;
        $this->debugMode = $debugMode;
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
        } catch (\Exception $e) {
            //todo: should log somewhere... but on the dev it throws exception
            // and on the production it does not log anywhere...
        }
    }
}