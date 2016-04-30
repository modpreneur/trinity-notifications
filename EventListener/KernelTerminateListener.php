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

//  To Fikus: Logger is send here directly. Interface does not contain
//  methods that our exception flow require(method addError in monolog/logger and following)
//  Since exchange with different implementation of LoggerInterface would bring incorrect
//  behavior I changed it. If it would be problem write me and i will do some inner logging
//  method, but it would be reinventing of wheel so for now I set here directly monolog.
//  Delete this when you read it :-)
//    /** @var  LoggerInterface */

    /** @var Logger */
    protected $logger;


    /** @var  bool */
    protected $debugMode;


    /**
     * KernelTerminateListener constructor.
     * @param NotificationManager $notificationManager
     * @param Logger $logger
     * @param bool $debugMode
     */
    public function __construct(NotificationManager $notificationManager, Logger $logger, bool $debugMode = false)
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
            /*
             * It may show multiple times store multiple logs when exception is thrown,
             * because of api calls (each call store same exception)
             */
            $this->logger->addError($e);
        }
    }
}