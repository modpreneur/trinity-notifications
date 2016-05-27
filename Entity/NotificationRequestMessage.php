<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.05.16
 * Time: 9:52
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\NotificationBundle\Exception\DataNotValidJsonException;
use Trinity\NotificationBundle\Exception\MissingClientIdException;
use Trinity\NotificationBundle\Exception\MissingClientSecretException;

/**
 * Class NotificationRequestMessage
 *
 * @package Trinity\NotificationBundle\Entity
 */
class NotificationRequestMessage extends Message
{
    const REQUEST = 'request';
    const NOTIFICATIONS = 'notifications';

    /** @var  Notification[] */
    protected $previousNotifications = [];

    /** @var  NotificationRequest */
    protected $request;


    /**
     * Encode message to JSON.
     *
     * @return string
     *
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     * @throws MissingClientIdException
     * @throws MissingClientSecretException
     */
    public function pack() : string
    {
        $data = [];
        $data[self::REQUEST] = $this->request->toArray();
        
        foreach ($this->previousNotifications as $notification) {
            $data[self::NOTIFICATIONS][] = $notification->toArray();
        }

        $this->jsonData = json_encode($data);

        return parent::pack();
    }
    

    /**
     * Unpack message
     *
     * @param string $messageJson
     *
     * @return NotificationRequestMessage
     *
     * @throws DataNotValidJsonException
     */
    public static function unpack(string $messageJson) : self
    {
        return self::createFromMessage(parent::unpack($messageJson));
    }


    /**
     * @param Message $message
     *
     * @return NotificationRequestMessage
     */
    public static function createFromMessage(Message $message) : self
    {
        $requestMessage = new self();

        //copy data from parent object
        $requestMessage->type = $message->type;
        $requestMessage->uid = $message->uid;
        $requestMessage->clientId = $message->clientId;
        $requestMessage->createdOn = $message->createdOn;
        $requestMessage->hash = $message->hash;
        $requestMessage->jsonData = $message->jsonData;
        $requestMessage->parentMessageUid = $message->parentMessageUid;
        $requestMessage->rawData = $message->rawData;

        $requestMessage->request = NotificationRequest::fromArray($requestMessage->rawData[self::REQUEST]);
        foreach ($requestMessage->rawData[self::NOTIFICATIONS] as $item) {
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
