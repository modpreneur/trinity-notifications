<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:56.
 */
namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotificationEvent.
 */
abstract class NotificationEvent extends Event
{
    const NAME = 'trinity.notifications.base_event';
}
