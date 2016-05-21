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
    //todo: cleanup the events!
    const BEFORE_DRIVER_EXECUTE = 'notification.beforeDriverExecute';

    const AFTER_DRIVER_EXECUTE = 'notification.afterDriverExecute';

    const BEFORE_BATCH_PUBLISH = 'notification.beforeBatchPublish';

    const BEFORE_MESSAGE_READ = 'message.beforeMessageRead';

    const MESSAGE_READ = 'message.messageRead';

    const BATCH_VALIDATED = 'notification.batchValidated';

    const BEFORE_PARSE_NOTIFICATION = 'notification.beforeParseNotification';

    const BEFORE_PERFORM_ENTITY_CHANGES = 'notification.beforePerformEntityChanges';

    const AFTER_PERFORM_ENTITY_CHANGES= 'notification.afterPerformEntityChanges';
    
    const AFTER_BATCH_PROCESSED = 'notification.afterBatchProcessed';

    const CONSUME_MESSAGE_ERROR= 'notification.consumeMessageError';

    const NOTIFICATION_REQUEST_EVENT = 'notification.notificationRequestEvent';

    const ASSOCIATION_ENTITY_NOT_FOUND_EXCEPTION_THROWN = 'notification.associationEntityNotFoundExceptionThrown';

    const ENABLE_NOTIFICATION = 'notification.enableNotification';

    const DISABLE_NOTIFICATION = 'notification.disableNotification';

    const CHANGES_DONE_EVENT = 'notification.changesDone';

}
