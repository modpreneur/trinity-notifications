<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Trinity\FrameworkBundle\Entity\IClient;

/**
 * Class Client.
 */
class Client implements IClient
{
    /** @var  bool */
    private $enable;

    /** @return string */
    public function getNotifyUrl()
    {
        return 'http://api.dev.clickandcoach.com/notify/';
    }

    /**
     * @param bool $e
     */
    public function setEnableNotification($e)
    {
        $this->enable = $e;
    }

    /**
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return $this->enable;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return '3lr3f9q9gbuoks84ogkk4wkc0sc4s4c84wkcscgcwcccwsowws';
    }
}
