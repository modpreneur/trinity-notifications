<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38.
 */
namespace Trinity\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class NotificationBatch.
 */
class NotificationBatch extends Message
{
    public const MESSAGE_TYPE = 'notification';

    /** @var  ArrayCollection<Notification> */
    protected $rawData;

    /**
     * NotificationBatch constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::MESSAGE_TYPE;
        $this->rawData = new ArrayCollection();
    }

    /**
     * Encode message to JSON or array.
     *
     * @return string
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     */
    public function pack() : string
    {
        $notificationsArray = [];
        /** @var Notification $notification */
        foreach ($this->rawData as $notification) {
            $notificationsArray[] = $notification->toArray();
        }

        $this->jsonData = \json_encode($notificationsArray);

        return parent::pack();
    }

    /**
     * Convert each notification to array and return array of that arrays.
     *
     * @return array
     */
    public function getArrayOfNotificationsConvertedToArray() : array
    {
        $data = [];
        /** @var Notification $notification */
        foreach ($this->rawData as $notification) {
            $data[] = $notification->toArray();
        }

        return $data;
    }

    /**
     * @return ArrayCollection<Notification>
     */
    public function getNotifications() : ArrayCollection
    {
        return $this->rawData;
    }

    /**
     * @param Notification $notification
     *
     * @return NotificationBatch
     */
    public function addNotification(Notification $notification) : NotificationBatch
    {
        if (!$this->rawData->contains($notification)) {
            $this->rawData->add($notification);
        }

        return $this;
    }

    /**
     * @param array $notifications
     */
    public function addNotifications(array $notifications)
    {
        foreach ($notifications as $notification) {
            $this->addNotification($notification);
        }
    }

    /**
     * @param Notification $notification
     *
     * @return NotificationBatch
     */
    public function removeNotification(Notification $notification) : NotificationBatch
    {
        $this->rawData->remove($notification);

        return $this;
    }

    /**
     * @param Message $message
     *
     * @return NotificationBatch
     */
    public static function createFromMessage(Message $message): Message
    {
        $notificationBatch = new self();
        $message->copyTo($notificationBatch);

        if (!$notificationBatch->rawData) {
            $notificationBatch->rawData = json_decode($notificationBatch->jsonData, true);
        }

        $notifications = [];
        //conversion succeeded
        if (\is_array($notificationBatch->rawData)) {
            foreach ($notificationBatch->rawData as $item) {
                $notifications[] = Notification::fromArray($item);
            }
        }

        $notificationBatch->rawData = new ArrayCollection($notifications);

        return $notificationBatch;
    }

    /**
     * Unpack message.
     *
     * @param string $messageJson
     *
     * @return NotificationBatch
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException
     */
    public static function unpack(string $messageJson): Message
    {
        return self::createFromMessage(parent::unpack($messageJson));
    }
}
