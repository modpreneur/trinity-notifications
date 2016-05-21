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
    const UID = 'uid';
    const DATA = 'data';
    const HASH = 'hash';
    const CLIENT_ID = 'clientId';
    const CREATED_ON = 'createdOn';
    const TYPE = 'messageType';

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


    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->uid = uniqid('', true);
        $this->createdOn = (new \DateTime('now'))->getTimestamp();
        $this->jsonData = null;
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
                [$this->uid, $this->clientId, json_encode($this->jsonData), $this->createdOn, $this->clientSecret, $this->type]
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
    public function isHashValid()
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

        if (!$this->hash) {
            $this->makeHash();
        }

        return $this->getAsJson();
    }


    /**
     * Unpack message
     *
     * @param string $messageJson
     *
     * @return Message
     *
     * @throws DataNotValidJsonException
     */
    public static function unpack(string $messageJson) : self
    {
        $messageObject = new self();

        $messageArray = \json_decode($messageJson, true);

        if ($messageArray === null) {
            throw new DataNotValidJsonException('Could not convert JSON to Message');
        }

        $messageObject->type = $messageArray[self::TYPE];
        $messageObject->uid = $messageArray[self::UID];
        $messageObject->clientId = $messageArray[self::CLIENT_ID];
        $messageObject->createdOn = $messageArray[self::CREATED_ON];
        $messageObject->hash = $messageArray[self::HASH];
        $messageObject->jsonData = $messageArray[self::DATA];
        $messageObject->rawData = \json_decode($messageObject->jsonData, true);

        return $messageObject;
    }


    /**
     * Get the Message as json
     *
     * @return string
     */
    protected function getAsJson()
    {
        return json_encode(
            [
                self::TYPE => $this->type,
                self::UID => $this->uid,
                self::CLIENT_ID => $this->clientId,
                self::CREATED_ON => $this->createdOn,
                self::HASH => $this->hash,
                self::DATA => $this->jsonData
            ]
        );
    }


    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }


    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }


    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }


    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }


    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }


    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }


    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }


    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }


    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }


    /**
     * @param string $jsonData
     */
    public function setJsonData($jsonData)
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
    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
    }
}
