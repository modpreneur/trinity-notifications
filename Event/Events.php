<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Event;

/**
 * Class Events.
 *
 * @author Tomáš Jančar
 */
final class Events
{
    /**
     * @Event("Trinity\NotificationBundle\Event\BeforeDriverExecuteEvent")
     */
    const BEFORE_DRIVER_EXECUTE = 'trinity.notifications.beforeDriverExecute';

    /**
     * @Event("Trinity\NotificationBundle\Event\AfterDriverExecuteEvent")
     */
    const AFTER_DRIVER_EXECUTE = 'trinity.notifications.afterDriverExecute';

    /**
     * @Event("Trinity\NotificationBundle\Event\BeforeParseNotificationEvent")
     */
    const BEFORE_PARSE_NOTIFICATION = 'trinity.notifications.beforeParseNotification';

    /**
     * @Event("Trinity\NotificationBundle\Event\BeforeNotificationBatchProcessEvent")
     */
    const BEFORE_NOTIFICATION_BATCH_PROCESS = 'trinity.notifications.beforeNotificationBatchProcess';
    
    /**
     * @Event("Trinity\NotificationBundle\Event\AfterNotificationBatchProcessEvent")
     */
    const AFTER_NOTIFICATION_BATCH_PROCESS = 'trinity.notifications.afterNotificationBatchProcess';

    /**
     * @Event("Trinity\NotificationBundle\Event\NotificationRequestEvent")
     */
    const NOTIFICATION_REQUEST = 'trinity.notifications.notificationRequestEvent';

    /**
     * @Event("Trinity\NotificationBundle\Event\AssociationEntityNotFoundEvent")
     */
    const ASSOCIATION_ENTITY_NOT_FOUND = 'trinity.notifications.associationEntityNotFound';

    /**
     * @Event("Trinity\NotificationBundle\Event\EnableNotificationEvent")
     */
    const ENABLE_NOTIFICATION = 'trinity.notifications.enableNotification';

    /**
     * @Event("Trinity\NotificationBundle\Event\DisableNotificationEvent")
     */
    const DISABLE_NOTIFICATION = 'trinity.notifications.disableNotification';

    /**
     * @Event("Trinity\NotificationBundle\Event\ChangesDoneEvent")
     */
    const CHANGES_DONE_EVENT = 'trinity.notifications.changesDone';

    /**
     * @Event("Trinity\NotificationBundle\Event\SendNotificationEvent")
     */
    const SEND_NOTIFICATION = 'trinity.notifications.sendNotification';

    /**
     * @Event("Trinity\NotificationBundle\Event\BeforeDeleteEntityEvent")
     */
    const BEFORE_DELETE_ENTITY = 'trinity.notifications.beforeDeleteEntity';
}
