<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\AppTests\Controllers;

use Trinity\NotificationBundle\Annotations\DisableNotification;


/**
 * Class ActiveController.
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
     * Test action.
     */
    public function activeNotificationAction()
    {
    }
}
