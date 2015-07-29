<?php
/**
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests\Controllers;
use Trinity\AnnotationsBundle\Annotations\Notification\DisableNotification;


/**
 * Class ActiveController
 * @package Trinity\NotificationBundle\Tests\Controllers
 */
class ActiveController
{
    /**
     * @DisableNotification()
     */
    public function disableNotificationAction()
    {
    }



    /**
     * Test action
     */
    public function activeNotificationAction()
    {
    }
}
