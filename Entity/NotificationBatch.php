<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38
 */

namespace Trinity\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class NotificationBatch
 *
 * @package Trinity\NotificationBundle\Entity
 */
class NotificationBatch extends Message
{
    /** @var  ArrayCollection<Notification> */
    protected $rawData;

    /**
     * NotificationBatch constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->rawData = new ArrayCollection();
    }


    /**
     * Pack batch
     *
     * @inheritdoc
     */
    public function pack() : string
    {
        $notificationsArray = [];
        /** @var Notification $notification */
        foreach ($this->rawData as $notification) {
            $notificationsArray[] = $notification->toArray();
        }

        $this->jsonData = \json_encode($notificationsArray);

        $this->makeHash();

        return $this->getAsJson();
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
        foreach ($this->rawData as $notification) {
            $data[] = $notification->toArray();
        }

        return $data;
    }


    /**
     * @return ArrayCollection<Notification>
     */
    public function getNotifications()
    {
        return $this->rawData;
    }


    /**
     * @param Notification $notification
     * @return $this
     */
    public function addNotification(Notification $notification)
    {
        if (!$this->rawData->contains($notification)) {
            $this->rawData->add($notification);
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
        $this->rawData->remove($notification);

        return $this;
    }
}

