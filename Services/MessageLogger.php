<?php

namespace Trinity\NotificationBundle\Services;

use Trinity\Bundle\MessagesBundle\Interfaces\MessageLoggerInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\NotificationBundle\Entity\MessageLog;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Interfaces\NotificationLogStorageInterface;

/**
 * Class MessageLogger.
 */
class MessageLogger implements MessageLoggerInterface
{
    /** @var  NotificationLogStorageInterface */
    protected $logStorage;

    /**
     * MessageLogger constructor.
     *
     * @param NotificationLogStorageInterface $logStorage
     */
    public function __construct(NotificationLogStorageInterface $logStorage)
    {
        $this->logStorage = $logStorage;
    }

    // TODO @JakubFajkus nice method to create empty message log
    /**
     * Log a message. This should log all incoming and outcoming messages.
     *
     * @param Message $messageObject Message to be logged
     * @param string  $messageJson   Message represented as json
     * @param string  $source        Source(e.g. server, client_1, etc.) of the message (= who has sent the message)
     * @param string  $destination   Destination of the message (e.g. server, client_1, etc.) (= who should receive the
     *                               message)
     * @param string  $status        Status of the message(ok, error).
     * @param string  $error         Error message from the message(if provided)
     */
    public function logMessage(
        Message $messageObject = null,
        string $messageJson = '',
        string $source = '',
        string $destination = '',
        string $status = '',
        string $error = ''
    ) {
        $log = new MessageLog();

        if ($messageObject) {
            $messageObject->copyTo($log);

            if (!$log->getJsonData() || $log->getJsonData() === '{}') {
                $data = [];
                if (is_array($log->getRawData()) || $log->getRawData() instanceof \Traversable) {
                    foreach ($log->getRawData() as $value) {
                        if ($value instanceof Notification) {
                            $data[] = $value->toArray();
                        }
                    }
                    $log->setJsonData(json_encode($data));
                } else {
                    $log->setJsonData(json_encode($log->getRawData()));
                }
            }
        }

        $log->setIsDead(false);
        $log->setLogCreatedAt(time());
        $log->setMessageJson($messageJson);
        $log->setSource($source);
        $log->setDestination($destination);
        $log->setStatus($status);
        $log->setError($error);

        //remove secret key from the message
        $log->setSecretKey('');

        $this->logStorage->createMessageLog($log);
//        $this->esLogger->writeIntoAsync('MessageLog', $log);
    }

    /**
     * Log a dead lettered message. The message was not accepted by the receiver. Set it's status to error.
     *
     * @param Message $messageObject Message to be logged
     * @param string  $messageJson   Message represented as json
     * @param string  $source        Source(e.g. server, client_1, etc.) of the message (= who has sent the message)
     * @param string  $destination   Destination of the message (e.g. server, client_1, etc.) (= who should receive the
     *                               message)
     */
    public function logDeadLetteredMessage(
        Message $messageObject = null,
        string $messageJson = '',
        string $source = '',
        string $destination = ''
    ) {
        $log = new MessageLog();

        if ($messageObject) {
            $messageObject->copyTo($log);

            if (!$log->getJsonData() || $log->getJsonData() === '{}') {
                $data = [];
                if (is_array($log->getRawData()) || $log->getRawData() instanceof \Traversable) {
                    foreach ($log->getRawData() as $value) {
                        if ($value instanceof Notification) {
                            $data[] = json_encode($value->toArray());
                        }
                    }
                    $log->setJsonData(json_encode($data));
                } else {
                    $log->setJsonData(json_encode($log->getRawData()));
                }
            }
        }
        $log->setIsDead(true);
        $log->setLogCreatedAt(time());
        $log->setMessageJson($messageJson);
        $log->setSource($source);
        $log->setDestination($destination);

        //remove secret key from the message
        $log->setSecretKey('');

        $this->logStorage->createMessageLog($log);
    }

    /**
     * Set status of the message with $messageId to $status.
     *
     * @param string $messageId     Message id
     * @param string $status        Status of the message(ok, error)
     * @param string $statusMessage Additional message to the status(practically additional information for 'error'
     *                              status).
     */
    public function setMessageStatus(string $messageId, string $status, string $statusMessage)
    {
        $this->logStorage->setMessageStatus($messageId, $status, $statusMessage);
    }
}
