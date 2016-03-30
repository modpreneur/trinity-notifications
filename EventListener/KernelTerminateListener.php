<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 17:06
 */

namespace Trinity\NotificationBundle\EventListener;


use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Trinity\NotificationBundle\Notification\NotificationManager;

class KernelTerminateListener
{
    /** @var  NotificationManager */
    protected $notificationManager;


    /**
     * KernelTerminateListener constructor.
     * @param NotificationManager $notificationManager
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }


    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->notificationManager->sendBatch();
    }
}