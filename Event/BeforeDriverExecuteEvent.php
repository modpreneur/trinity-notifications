<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 09.04.16
 * Time: 11:26
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class BeforeDriverExecuteEvent
 */
class BeforeDriverExecuteEvent extends DriverExecuteEvent
{
    const NAME = 'trinity.notifications.beforeDriverExecute';
}
