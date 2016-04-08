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
    const BEFORE_NOTIFICATION_SEND = 'notification.beforeNotificationSend';

    const AFTER_NOTIFICATION_SEND = 'notification.afterNotificationSend';

    const ERROR_NOTIFICATION = 'notification.error';

    const SUCCESS_NOTIFICATION = 'notification.success';

    const BATCH_VALIDATED = 'notification.batchValidated';

    const BEFORE_MESSAGE_READ = 'notification.beforeMessageRead';

    const BEFORE_PARSE_NOTIFICATION = 'notification.beforeParseNotification';

    const BEFORE_PERFORM_ENTITY_CHANGES = 'notification.beforePerformEntityChanges';

    const AFTER_PERFORM_ENTITY_CHANGES= 'notification.afterPerformEntityChanges';

    const CONSUME_MESSAGE_ERROR= 'notification.consumeMessageError';
}
