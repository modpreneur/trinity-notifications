<?php

namespace Trinity\NotificationBundle\Notification;

use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\NotificationBatch;

/**
 * Class BatchManager.
 */
class BatchManager extends MessageSender
{
    /** @var NotificationBatch[] */
    protected $messages = [];

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
}
