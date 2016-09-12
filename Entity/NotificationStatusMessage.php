<?php

namespace Trinity\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class NotificationStatusMessage.
 */
class NotificationStatusMessage extends Message
{
    const MESSAGE_TYPE = 'notificationStatus';

    /** @var ArrayCollection<NotificationStatus>  */
    protected $statuses;

    /**
     * NotificationStatusMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::MESSAGE_TYPE;
        $this->statuses = new ArrayCollection();
    }

    /**
     * @return ArrayCollection<NotificationStatus>
     */
    public function getNotificationStatus()
    {
        return $this->statuses;
    }

    /**
     * @param NotificationStatus $notificationStatus
     *
     * @return $this
     */
    public function addNotificationStatus(NotificationStatus $notificationStatus)
    {
        if (!$this->statuses->contains($notificationStatus)) {
            $this->statuses->add($notificationStatus);
        }

        return $this;
    }

    /**
     * @param NotificationStatus $notificationStatus
     *
     * @return $this
     */
    public function removeNotificationStatus(NotificationStatus $notificationStatus)
    {
        $this->statuses->remove($notificationStatus);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAllStatuses()
    {
        return $this->statuses;
    }

    /**
     * Encode message to JSON.
     *
     * @param bool $getAsArray
     *
     * @return string
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     */
    public function pack(bool $getAsArray = false) : string
    {
        $statuses = [];
        /** @var NotificationStatus $status */
        foreach ($this->statuses as $status) {
            $statuses[] = $status->toArray();
        }

        $this->jsonData = json_encode($statuses);

        return parent::pack($getAsArray);
    }

    /**
     * @param Message $message
     *
     * @return NotificationStatusMessage
     */
    public static function createFromMessage(Message $message) : self
    {
        $statusMessage = new self();
        $message->copyTo($statusMessage);
        $decoded = json_decode($statusMessage->jsonData);

        $statuses = [];
        //conversion succeeded
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $statuses[] = NotificationStatus::fromArray($item);
            }
        }

        $statusMessage->statuses = new ArrayCollection($statuses);

        return $statusMessage;
    }
}
