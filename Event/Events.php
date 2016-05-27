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
    const BEFORE_DRIVER_EXECUTE = 'notification.beforeDriverExecute';

    const AFTER_DRIVER_EXECUTE = 'notification.afterDriverExecute';
    
    const BEFORE_MESSAGE_PUBLISH = 'message.beforeMessagePublish';

    const BEFORE_MESSAGE_READ = 'message.beforeMessageRead';

    const MESSAGE_READ = 'message.messageRead';

    const DEAD_LETTERED_MESSAGE_READ = 'message.deadLetteredMessageRead';

    const BEFORE_PARSE_NOTIFICATION = 'notification.beforeParseNotification';
    
    const CONSUME_MESSAGE_ERROR = 'notification.consumeMessageError';

    const NOTIFICATION_REQUEST_EVENT = 'notification.notificationRequestEvent';

    const ASSOCIATION_ENTITY_NOT_FOUND_EXCEPTION_THROWN = 'notification.associationEntityNotFoundExceptionThrown';

    const ENABLE_NOTIFICATION = 'notification.enableNotification';

    const DISABLE_NOTIFICATION = 'notification.disableNotification';

    const CHANGES_DONE_EVENT = 'notification.changesDone';

    const AFTER_MESSAGE_UNPACKED = 'message.afterMessageUnpacked';

    const SET_MESSAGE_STATUS = 'message.setMessageStatus';
}
