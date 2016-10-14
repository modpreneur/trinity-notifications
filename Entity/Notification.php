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
    const ENTITY_ID = 'entityId';
    const UID = 'uid';

    /** @var string */
    protected $messageId = '';

    /** @var array Array of notification data(e.g. name, description) */
    protected $data = [];

    /** @var  string Entity alias, e.g. product, user */
    protected $entityName = '';

    /** @var int  */
    protected $entityId = 0;

    /** @var array */
    protected $changeSet = [];

    /** @var string HTTP method of the message. */
    protected $method = '';

    /** @var  bool If the notification is forced and should not be checked for any changeset violation */
    protected $isForced = false;

    /** @var  int */
    protected $createdAt;

    /** @var  string */
    protected $clientId = '';

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
     * @param array|string $data
     *
     * @return Notification
     */
    public function setData($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
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
     * @param array|string $changeSet
     *
     * @return Notification
     */
    public function setChangeSet($changeSet)
    {
        if (is_string($changeSet)) {
            $changeSet = json_decode($changeSet, true);
        }

        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     */
    public function setIsForced(bool $isForced)
    {
        $this->isForced = $isForced;

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;

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
            self::DATA => $this->data,
            self::CHANGE_SET => $this->changeSet,
            self::IS_FORCED => $this->isForced,
            self::CREATED_AT => $this->createdAt,
            self::CLIENT_ID => $this->clientId,
            self::ENTITY_NAME => $this->entityName,
            self::UID => $this->uid,
            self::ENTITY_ID => $this->entityId
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
        $notificationObject->uid = $notificationArray[self::UID];
        $notificationObject->entityId = $notificationArray[self::ENTITY_ID];

        return $notificationObject;
    }

    /**
     * Parse json which contains array of notifications to array of Notification objects
     *
     * @param string $json
     *
     * @return array
     */
    public static function fromJson(string $json)
    {
        /** @var array $notificationsArrays */
        $notificationsArrays = json_decode($json, true);

        $notifications = [];
        //conversion succeeded
        if (is_array($notificationsArrays)) {
            /** @var array $notificationsArrays */
            foreach ($notificationsArrays as $item) {
                $notifications[] = Notification::fromArray($item);
            }
        }

        return $notifications;
    }
}
