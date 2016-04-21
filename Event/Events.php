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

    const BEFORE_BATCH_PUBLISH= 'notification.beforeBatchPublish';

//    const ERROR_NOTIFICATION = 'notification.error';

//    const SUCCESS_NOTIFICATION = 'notification.success';

    const BEFORE_MESSAGE_READ = 'notification.beforeMessageRead';

    const BATCH_VALIDATED = 'notification.batchValidated';

    const BEFORE_PARSE_NOTIFICATION = 'notification.beforeParseNotification';

    const BEFORE_PERFORM_ENTITY_CHANGES = 'notification.beforePerformEntityChanges';

    const AFTER_PERFORM_ENTITY_CHANGES= 'notification.afterPerformEntityChanges';
    
    const AFTER_BATCH_PROCESSED = 'notification.afterBatchProcessed';

    const CONSUME_MESSAGE_ERROR= 'notification.consumeMessageError';

}
