<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38
 */

namespace Trinity\NotificationBundle\Entity;

/**
 * Class Notification
 *
 * @package Trinity\NotificationBundle\Entity
 */
class Notification
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    const METHOD = 'method';
    const DATA = 'data';
    const MESSAGE_ID = 'messageId';

    /** @var string */
    protected $messageId;


    /** @var array Array of notification data(e.g. name, description) */
    protected $data;


    /** @var string HTTP method of the message. */
    protected $method;


    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }


    /**
     * @param string $method
     *
     * @return Notification
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }


    /**
     * @return string
     */
    public function getMessageId() : string
    {
        return $this->messageId;
    }


    /**
     * @param string $messageId
     *
     * @return Notification
     */
    public function setMessageId(string $messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }


    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }


    /**
     * @param array $data
     *
     * @return Notification
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }


    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            self::MESSAGE_ID => $this->messageId,
            self::METHOD => $this->method,
            self::DATA => $this->data
        ];
    }


    /**
     * Create Notification from array
     *
     * @param array $notificationArray
     *
     * @return Notification
     */
    public static function fromArray(array $notificationArray = []) : self
    {
        $notificationObject = new self();

        $notificationObject->messageId = $notificationArray[self::MESSAGE_ID];
        $notificationObject->data = $notificationArray[self::DATA];
        $notificationObject->method = $notificationArray[self::METHOD];

        return $notificationObject;
    }
}
