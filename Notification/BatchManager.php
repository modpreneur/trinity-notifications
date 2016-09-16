<?php

namespace Trinity\NotificationBundle\Notification;

use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;

/**
 * Class BatchManager.
 */
class BatchManager extends MessageSender
{
    /** @var NotificationBatch[] */
    protected $messages = [];

    /** @var  NotificationLoggerInterface */
    protected $notificationLogger;

    /**
     * Add notifications to the batch. Create a new batch if it does not exist.
     * This method ensures that there is only one batch for each client.
     *
     * @param string $clientId
     * @param array  $notifications
     *
     * @return NotificationBatch Created batch or batch which was added the data.
     */
    public function createBatch(string $clientId, array $notifications = [])
    {
        $returnBatch = null;

        foreach ($this->messages as $batch) {
            if ($batch->getClientId() == $clientId) {
                $returnBatch = $batch;
                break;
            }
        }

        if ($returnBatch) {
            $returnBatch->addNotifications($notifications);
        } else {
            $returnBatch = new NotificationBatch();
            $returnBatch->addNotifications($notifications);
            $returnBatch->setClientId($clientId);

            $this->messages[] = $returnBatch;
        }

        return $returnBatch;
    }

    /**
     * @param Message $message
     *
     * @throws \InvalidArgumentException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     */
    public function sendMessage(Message $message)
    {
        parent::sendMessage($message);
        $message = NotificationBatch::createFromMessage($message);

        $this->setNotificationStatuses($message);
    }

    /**
     * @return NotificationBatch[]
     */
    public function getBatches() : array
    {
        return $this->messages;
    }

    /**
     * @param NotificationBatch[] $messages
     *
     * @return BatchManager
     */
    public function setBatches(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param NotificationLoggerInterface $notificationLogger
     */
    public function setNotificationLogger(NotificationLoggerInterface $notificationLogger)
    {
        $this->notificationLogger = $notificationLogger;
    }

    /**
     * @param NotificationBatch $batch
     *
     * @throws \InvalidArgumentException
     */
    protected function setNotificationStatuses(NotificationBatch $batch)
    {
        $statuses = [];
        /** @var Notification $notification */
        foreach ($batch->getNotifications() as $notification) {
            $notificationStatus = new NotificationStatus();
            $notificationStatus->setNotificationId($notification->getUid());
            $notificationStatus->setStatus(NotificationStatus::STATUS_SENT);

            $statuses[] = $notificationStatus;
        }

        $this->notificationLogger->setNotificationStatuses($statuses);
    }
}
