<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Event;

/**
 * Class StoreEvents.
 *
 * @author Tomáš Jančar
 */
final class Events
{
//todo: cleanup!
    const BEFORE_DRIVER_EXECUTE = 'notification.beforeDriverExecute';

    const AFTER_DRIVER_EXECUTE = 'notification.afterDriverExecute';
    
    const DEAD_LETTERED_MESSAGE_READ = 'message.deadLetteredMessageRead';

    const BEFORE_PARSE_NOTIFICATION = 'notification.beforeParseNotification';

    const CONSUME_MESSAGE_ERROR = 'notification.consumeMessageError';

    const NOTIFICATION_REQUEST_EVENT = 'trinity.notifications.notificationRequestEvent';

    const ASSOCIATION_ENTITY_NOT_FOUND_EXCEPTION_THROWN = 'trinity.notifications.associationEntityNotFoundExceptionThrown';

    const ENABLE_NOTIFICATION = 'notification.enableNotification';

    const DISABLE_NOTIFICATION = 'notification.disableNotification';

    const CHANGES_DONE_EVENT = 'trinity.notifications.changesDone';

    const SEND_NOTIFICATION = 'trinity.notifications.sendNotification';

    /** todo: add to all Events.php
     * @Event("Trinity\NotificationBundle\Event\BeforeDeleteEntityEvent")
     */
    const BEFORE_DELETE_ENTITY = 'trinity.notifications.beforeDeleteEntity';
}
