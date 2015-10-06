<?php

namespace Trinity\NotificationBundle\Tests\Entity;

use Trinity\NotificationBundle\Drivers\ApiDriver\IApiClient;


/**
 * Class Client.
 */
class Client implements IApiClient
{
    /** @var  bool */
    private $enable;


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


    /** @return string */
    public function getNotificationUri()
    {
        return 'http://api.dev.clickandcoach.com/notify/';
    }
}
