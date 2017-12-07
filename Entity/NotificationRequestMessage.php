<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.05.16
 * Time: 9:52.
 */
namespace Trinity\NotificationBundle\Entity;

use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class NotificationRequestMessage.
 */
class NotificationRequestMessage extends Message
{
    private const REQUEST_KEY = 'request';
    private const NOTIFICATIONS_KEY = 'notifications';
    public const MESSAGE_TYPE = 'notificationRequest';

    /** @var  Notification[] */
    protected $previousNotifications = [];

    /** @var  NotificationRequest */
    protected $request;

    /**
     * NotificationRequestMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::MESSAGE_TYPE;
    }

    /**
     * Encode message to JSON.
     * @return string
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     */
    public function pack() : string
    {
        $data = [];
        $data[self::REQUEST_KEY] = $this->request->toArray();

        foreach ($this->previousNotifications as $notification) {
            $data[self::NOTIFICATIONS_KEY][] = $notification->toArray();
        }

        $this->jsonData = json_encode($data);

        return parent::pack();
    }

    /**
     * Unpack message.
     *
     * @param string $messageJson
     *
     * @return NotificationRequestMessage
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException
     */
    public static function unpack(string $messageJson): Message
    {
        return self::createFromMessage(parent::unpack($messageJson));
    }

    /**
     * @param Message $message
     *
     * @return NotificationRequestMessage
     */
    public static function createFromMessage(Message $message): Message
    {
        $requestMessage = new self();
        $message->copyTo($requestMessage);

        $requestMessage->request = NotificationRequest::fromArray($requestMessage->rawData[self::REQUEST_KEY]);
        foreach ($requestMessage->rawData[self::NOTIFICATIONS_KEY] as $item) {
            $requestMessage->previousNotifications[] = Notification::fromArray($item);
        }

        return $requestMessage;
    }

    /**
     * @return Notification[]
     */
    public function getPreviousNotifications() : array
    {
        return $this->previousNotifications;
    }

    /**
     * @param Notification[] $previousNotifications
     */
    public function setPreviousNotifications(array $previousNotifications)
    {
        $this->previousNotifications = $previousNotifications;
    }

    /**
     * @param Notification $notification
     */
    public function addPreviousNotification(Notification $notification)
    {
        $this->previousNotifications[] = $notification;
    }

    /**
     * @return NotificationRequest
     */
    public function getRequest() : NotificationRequest
    {
        return $this->request;
    }

    /**
     * @param NotificationRequest $request
     */
    public function setRequest(NotificationRequest $request)
    {
        $this->request = $request;
    }
}
