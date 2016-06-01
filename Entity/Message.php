<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.05.16
 * Time: 12:02
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\NotificationBundle\Exception\DataNotValidJsonException;
use Trinity\NotificationBundle\Exception\MissingClientIdException;
use Trinity\NotificationBundle\Exception\MissingClientSecretException;
use Trinity\NotificationBundle\Exception\MissingMessageTypeException;

/**
 * Class Message
 *
 * @package Trinity\NotificationBundle\Entity
 */
class Message
{
    const UID_KEY = 'uid';
    const DATA_KEY = 'data';
    const HASH_KEY = 'hash';
    const CLIENT_ID_KEY = 'clientId';
    const CREATED_ON_KEY = 'createdOn';
    const MESSAGE_TYPE_KEY = 'messageType';
    const PARENT_MESSAGE_UID_KEY = 'parent';
    const MESSAGE_TYPE = 'message';

    /** @var  string */
    protected $uid;


    /** @var string */
    protected $clientId;


    /** @var  string */
    protected $clientSecret;


    /** @var  string */
    protected $jsonData;


    /** @var  mixed Data in raw format(numbers, objects, arrays) */
    protected $rawData;


    /** @var  \DateTime */
    protected $createdOn;


    /** @var  string */
    protected $hash;


    /** @var  string */
    protected $type;


    /** @var  string */
    protected $parentMessageUid;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->type = self::MESSAGE_TYPE;
        $this->uid = uniqid('', true);
        $this->createdOn = (new \DateTime('now'))->getTimestamp();
        $this->jsonData = null;
        $this->parentMessageUid = null;
    }


    /**
     * Make hash from the object's data
     *
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     */
    public function makeHash()
    {
        if (!$this->clientSecret) {
            throw new MissingClientSecretException('No client secret defined while trying to make hash.');
        }

        if (!$this->clientId) {
            throw new MissingClientIdException('No client id defined while trying to make hash.');
        }

        $this->hash = hash(
            'sha256',
            implode(
                ',',
                [
                    $this->uid,
                    $this->clientId,
                    json_encode($this->jsonData),
                    $this->createdOn,
                    $this->clientSecret,
                    $this->type,
                    $this->parentMessageUid
                ]
            )
        );
    }


    /**
     * Check if the current hash is equal to newly generated hash.
     *
     * @return bool
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     */
    public function isHashValid() : bool
    {
        $oldHash = $this->hash;
        $this->makeHash();

        return $oldHash === $this->hash;
    }


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
        if ($this->type === null) {
            throw new MissingMessageTypeException('Trying to pack a message without type');
        }

        if ($this->jsonData === null) {
            $this->jsonData = \json_encode($this->rawData);
        }

        $this->makeHash();

        return $this->getAsJson();
    }


    /**
     * Unpack message
     *
     * Method can not have return type because PHP sucks... @see https://wiki.php.net/rfc/return_types
     *
     * @param string $messageJson
     *
     * @return Message
     *
     * @throws DataNotValidJsonException
     */
    public static function unpack(string $messageJson)
    {
        $messageObject = new self();

        $messageArray = \json_decode($messageJson, true);

        if ($messageArray === null) {
            throw new DataNotValidJsonException('Could not convert JSON to Message');
        }

        $messageObject->type = $messageArray[self::MESSAGE_TYPE_KEY];
        $messageObject->uid = $messageArray[self::UID_KEY];
        $messageObject->clientId = $messageArray[self::CLIENT_ID_KEY];
        $messageObject->createdOn = $messageArray[self::CREATED_ON_KEY];
        $messageObject->hash = $messageArray[self::HASH_KEY];
        $messageObject->jsonData = $messageArray[self::DATA_KEY];
        $messageObject->rawData = \json_decode($messageObject->jsonData, true);
        $messageObject->parentMessageUid = $messageArray[self::PARENT_MESSAGE_UID_KEY];

        return $messageObject;
    }


    /**
     * Get the Message as json
     *
     * @return string
     */
    public function getAsJson() : string
    {
        return json_encode(
            [
                self::MESSAGE_TYPE_KEY => $this->type,
                self::UID_KEY => $this->uid,
                self::CLIENT_ID_KEY => $this->clientId,
                self::CREATED_ON_KEY => $this->createdOn,
                self::HASH_KEY => $this->hash,
                self::DATA_KEY => $this->jsonData,
                self::PARENT_MESSAGE_UID_KEY => $this->parentMessageUid
            ]
        );
    }


    /**
     * @param Message $message
     *
     * @return Message
     */
    public function copyTo(Message $message)
    {
        $message->type = $this->type;
        $message->uid = $this->uid;
        $message->clientId = $this->clientId;
        $message->createdOn = $this->createdOn;
        $message->hash = $this->hash;
        $message->jsonData = $this->jsonData;
        $message->parentMessageUid = $this->parentMessageUid;
        $message->rawData = $this->rawData;

        return $message;
    }


    /**
     * @param Message $message
     *
     * @return Message
     */
    public static function createFromMessage(Message $message)
    {
        $returnMessage = new self();

        $message->copyTo($returnMessage);

        return $returnMessage;
    }


    /**
     * @return string
     */
    public function getUid() : string
    {
        return $this->uid;
    }


    /**
     * @param string $uid
     */
    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }


    /**
     * @return string
     */
    public function getClientId() : string
    {
        return $this->clientId;
    }


    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }


    /**
     * @return string
     */
    public function getClientSecret() : string
    {
        return $this->clientSecret;
    }


    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedOn() : \DateTime
    {
        return $this->createdOn;
    }


    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }


    /**
     * @return string
     */
    public function getHash() : string
    {
        return $this->hash;
    }


    /**
     * @param string $hash
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;
    }


    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getJsonData() : string
    {
        return $this->jsonData;
    }


    /**
     * @param string $jsonData
     */
    public function setJsonData(string $jsonData)
    {
        $this->jsonData = $jsonData;
    }


    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->rawData;
    }


    /**
     * @param mixed $rawData
     */
    public function setRawData(mixed $rawData)
    {
        $this->rawData = $rawData;
    }


    /**
     * @return string
     */
    public function getParentMessageUid() : string
    {
        return $this->parentMessageUid;
    }


    /**
     * @param string $parentMessageUid
     */
    public function setParentMessageUid(string $parentMessageUid)
    {
        $this->parentMessageUid = $parentMessageUid;
    }
}
