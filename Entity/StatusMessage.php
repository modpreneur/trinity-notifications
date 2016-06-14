<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.05.16
 * Time: 9:03
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\NotificationBundle\Exception\InvalidMessageStatusException;

/**
 * Class StatusMessage
 *
 * Is used to confirm success or failure of the parent message
 *
 * @package Trinity\NotificationBundle\Entity
 */
class StatusMessage extends Message
{
    const STATUS_KEY = 'status';
    const STATUS_MESSAGE_KEY = 'message';

    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';

    const MESSAGE_TYPE = 'status';


    /**
     * StatusMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::MESSAGE_TYPE;
        $this->rawData = [self::STATUS_KEY => self::STATUS_OK, self::STATUS_MESSAGE_KEY => ''];
    }


    /**
     * Encode message to JSON.
     *
     * @param bool $getAsArray
     *
     * @return string
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     */
    public function pack(bool $getAsArray = false) : string
    {
        $this->jsonData = json_encode($this->rawData);

        return parent::pack($getAsArray);
    }


    /**
     * @param Message $message
     *
     * @return StatusMessage
     */
    public static function createFromMessage(Message $message) : self
    {
        $statusMessage = new self;
        $message->copyTo($statusMessage);

        return $statusMessage;
    }


    /**
     * Was the parent message ok
     *
     * @return bool
     */
    public function isOkay() : bool
    {
        return $this->rawData === 'ok';
    }


    /**
     * @param string $statusMessage
     */
    public function setOk(string $statusMessage = 'ok')
    {
        $this->rawData[self::STATUS_KEY] = 'ok';
        $this->rawData[self::STATUS_MESSAGE_KEY] = $statusMessage;
    }


    /**
     * @param string $errorMessage
     */
    public function setError(string $errorMessage)
    {
        $this->rawData[self::STATUS_KEY] = self::STATUS_ERROR;
        $this->rawData[self::STATUS_MESSAGE_KEY] = $errorMessage;
    }


    /**
     * @return string
     */
    public function getStatusMessage() : string
    {
        return $this->rawData[self::STATUS_MESSAGE_KEY];
    }


    /**
     * @param string $statusMessage
     */
    public function setStatusMessage(string $statusMessage)
    {
        $this->rawData[self::STATUS_MESSAGE_KEY] = $statusMessage;
    }


    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->rawData[self::STATUS_KEY];
    }


    /**
     * @param string $status
     *
     * @throws \Trinity\NotificationBundle\Exception\InvalidMessageStatusException
     */
    public function setStatus(string $status)
    {
        if (!($status === self::STATUS_OK || $status === self::STATUS_ERROR)) {
            throw new InvalidMessageStatusException(
                "Status '$status' is not valid. Choose one from '" .
                self::STATUS_ERROR . "' or '" . self::STATUS_OK . "'"
            );
        }

        $this->rawData[self:: STATUS_KEY] = $status;
    }
}
