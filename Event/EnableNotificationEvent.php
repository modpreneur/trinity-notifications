<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.05.16
 * Time: 14:42.
 */
namespace Trinity\NotificationBundle\Event;

/**
 * Class EnableListeningEvent.
 */
class EnableNotificationEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.enableNotification';
}
