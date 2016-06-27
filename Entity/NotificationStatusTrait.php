<?php

namespace Trinity\NotificationBundle\Entity;

use Trinity\Component\Core\Interfaces\ClientInterface;


/**
 * Class NotificationStatusTrait
 *
 * @package Trinity\NotificationBundle\Entity
 */
trait NotificationStatusTrait
{
    public static $NOT_SYNCHRONIZED = 'NOT_SYNCHRONIZED';
    public static $SYNCHRONIZATION_IN_PROGRESS = 'SYNCHRONIZATION_IN_PROGRESS';
    public static $SYNCHRONIZATION_ERROR = 'SYNCHRONIZATION_ERROR';
    public static $SYNCHRONIZED = 'SYNCHRONIZED';
    public static $SYNCHRONIZATION_MESSAGE_TYPE_FIELD = 'messageType';
    public static $SYNCHRONIZATION_MESSAGE_UID_FIELD = 'messageUid';
    public static $SYNCHRONIZATION_CHANGED_AT = 'changedAt';

    /**
     * @var []
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $notificationStatus;

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageType
     * @param string          $statusMessageUid
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationStatus(
        ClientInterface $client,
        string $statusMessageType,
        string $statusMessageUid,
        \DateTime $changedAt = null
    ) {
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_TYPE_FIELD] = $statusMessageType;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_UID_FIELD] = $statusMessageUid;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_CHANGED_AT] = ($changedAt !== null) ?: new \DateTime('now');
    }

    /**
     * @return array|null
     */
    public function getNotificationStatus()
    {
        return $this->notificationStatus;
    }

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationInProgress(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    ) {
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_TYPE_FIELD] = self::$SYNCHRONIZATION_IN_PROGRESS;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_UID_FIELD] = $statusMessageUid;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_CHANGED_AT] = ($changedAt !== null) ?: new \DateTime('now');
    }

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationNotSynchronized(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    ) {
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_TYPE_FIELD] = self::$NOT_SYNCHRONIZED;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_UID_FIELD] = $statusMessageUid;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_CHANGED_AT] = ($changedAt !== null) ?: new \DateTime('now');
    }

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationError(ClientInterface $client, string $statusMessageUid, \DateTime $changedAt = null)
    {
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_TYPE_FIELD] = self::$SYNCHRONIZATION_ERROR;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_UID_FIELD] = $statusMessageUid;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_CHANGED_AT] = ($changedAt !== null) ?: new \DateTime('now');
    }

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationSynchronized(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    ) {
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_TYPE_FIELD] = self::$SYNCHRONIZED;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_MESSAGE_UID_FIELD] = $statusMessageUid;
        $this->notificationStatus[$client->getId()][self::$SYNCHRONIZATION_CHANGED_AT] = ($changedAt !== null) ?: new \DateTime('now');
    }

    /**
     * @param ClientInterface $client
     *
     * @return null
     */
    public function getNotificationStatusForClient(ClientInterface $client)
    {
        if (array_key_exists($client->getId(), $this->notificationStatus)) {
            return $this->notificationStatus[$client->getId()];
        } else {
            return null;
        }
    }
}