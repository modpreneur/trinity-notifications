<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 09.04.16
 * Time: 11:27
 */

namespace Trinity\NotificationBundle\Event;

/**
 * Class AfterDriverExecuteEvent
 */
class AfterDriverExecuteEvent extends DriverExecuteEvent
{
    const NAME = 'trinity.notifications.afterDriverExecute';
}
