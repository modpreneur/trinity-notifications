<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38
 */

namespace Trinity\NotificationBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class NotificationBatch
{
    const UID = "uid";
    const CLIENT_ID = "clientId";
    const NOTIFICATIONS = "notifications";
    const TIMESTAMP = "timestamp";
    const HASH = "hash";


    /**
     * @var string Unique id
     */
    protected $uId;


    /**
     * @var string Client id
     */
    protected $clientId;


    /**
     * @var ArrayCollection<Notification>
     */
    protected $notifications;


    /**
     * @var int
     */
    protected $timestamp;


    /**
     * @var string Hashed data
     */
    protected $hash;


    /**
     * @var string Is not packed to the JSON!
     */
    protected $clientSecret;


    /**
     * NotificationBatch constructor.
     */
    public function __construct()
    {
        $this->uId = uniqid("", true);
        $this->timestamp = (new \DateTime("now"))->getTimestamp();
        $this->notifications = new ArrayCollection();
    }


    /**
     * Make hash from the object's data
     */
    public function makeHash()
    {
        $notificationsString = json_encode($this->getArrayOfNotificationsConvertedToArray());

        $this->hash = hash(
            'sha256',
            implode(",", [$this->uId, $this->clientId, $notificationsString, $this->timestamp, $this->clientSecret])
        );
    }


    /**
     * Check if the current hash is equal to newly generated hash.
     *
     * @return bool
     */
    public function isHashValid()
    {
        $oldHash = $this->hash;
        $this->makeHash();

        return $oldHash === $this->hash;
    }


    /**
     * Encode batch to JSON.
     *
     * @return string
     */
    public function packBatch()
    {
        $data = $this->getArrayOfNotificationsConvertedToArray();

        if (!$this->hash) {
            $this->makeHash();
        }

        return json_encode(
            [
                self::UID => $this->uId,
                self::CLIENT_ID => $this->clientId,
                self::TIMESTAMP => $this->timestamp,
                self::HASH => $this->hash,
                self::NOTIFICATIONS => $data
            ]
        );
    }

    /**
     * Fill object with $json data
     *
     * @param string $json
     * @return null
     */
    public function unpackBatch(string $json)
    {
        $batch = json_decode($json, true);

        if (!$batch) {
            return null;
        }

        $this->uId = $batch[self::UID];
        $this->clientId = $batch[self::CLIENT_ID];
        $this->timestamp = $batch[self::TIMESTAMP];
        $this->hash = $batch[self::HASH];

        foreach ($batch[self::NOTIFICATIONS] as $notificationArray) {
            $notification = new Notification();
            $notification->fromArray($notificationArray, $this->getUId());
            $this->addNotification($notification);
        }

        return $this;
    }


    /**
     * Convert each notification to array and return array of that arrays.
     *
     * @return array
     */
    public function getArrayOfNotificationsConvertedToArray()
    {
        $data = [];
        /** @var Notification $notification */
        foreach ($this->notifications as $notification) {
            $data[] = $notification->toArray();
        }

        return $data;
    }


    /**
     * @return string
     */
    public function getUId()
    {
        return $this->uId;
    }


    /**
     * @param string $uId
     * @return NotificationBatch
     */
    public function setUId($uId)
    {
        $this->uId = $uId;

        return $this;
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
     * @return NotificationBatch
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }


    /**
     * @return ArrayCollection<Notification>
     */
    public function getNotifications()
    {
        return $this->notifications;
    }


    /**
     * @param Notification $notification
     * @return $this
     */
    public function addNotification(Notification $notification)
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
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
     * @return $this
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->remove($notification);

        return $this;
    }


    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }


    /**
     * @param int $timestamp
     * @return NotificationBatch
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
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
     * @return NotificationBatch
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

}
