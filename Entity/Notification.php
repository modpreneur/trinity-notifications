<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38.
 */
namespace Trinity\NotificationBundle\Entity;

/**
 * Class Notification.
 */
class Notification
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    const METHOD = 'method';
    const DATA = 'data';
    const CHANGE_SET = 'changeSet';
    const MESSAGE_ID = 'messageId';
    const IS_FORCED = 'isForced';
    const CREATED_AT = 'createdAt';
    const CLIENT_ID = 'clientId';
    const ENTITY_NAME = 'entityName';

    /** @var string */
    protected $messageId;

    /** @var array Array of notification data(e.g. name, description) */
    protected $data;

    /** @var  string Entity alias, e.g. product, user */
    protected $entityName;

    /** @var array */
    protected $changeSet = [];

    /** @var string HTTP method of the message. */
    protected $method;

    /** @var  bool If the notification is forced and should not be checked for any changeset violation */
    protected $isForced;

    /** @var  int */
    protected $createdAt;

    /** @var  string */
    protected $clientId;

    /** @var  string */
    protected $uid;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->createdAt = time();
        $this->uid = uniqid('', true);
    }

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
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    /**
     * @param array $changeSet
     *
     * @return Notification
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param boolean $isForced
     */
    public function setIsForced(bool $isForced)
    {
        $this->isForced = $isForced;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getClientId(): string
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
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            self::MESSAGE_ID => $this->messageId,
            self::METHOD => $this->method,
            self::DATA => $this->data,
            self::CHANGE_SET => $this->changeSet,
            self::IS_FORCED => $this->isForced,
            self::CREATED_AT => $this->createdAt,
            self::CLIENT_ID => $this->clientId,
            self::ENTITY_NAME => $this->entityName,
        ];
    }

    /**
     * Create Notification from array.
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
        $notificationObject->changeSet = $notificationArray[self::CHANGE_SET];
        $notificationObject->isForced = $notificationArray[self::IS_FORCED];
        $notificationObject->createdAt = $notificationArray[self::CREATED_AT];
        $notificationObject->clientId = $notificationArray[self::CLIENT_ID];
        $notificationObject->entityName = $notificationArray[self::ENTITY_NAME];

        return $notificationObject;
    }
}
