<?php

namespace Trinity\NotificationBundle\Tests\Controllers;

use Trinity\AnnotationsBundle\Annotations\Notification\DisableNotification;

class ActiveController
{
    /**
     * @DisableNotification()
     */
    public function disableNotificationAction()
    {
    }

    public function activeNotificationAction()
    {
    }
}
