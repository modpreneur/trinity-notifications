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
        return '8rsxbgk63b40k8g0cs00ks0s8s0co0884sk4swgk04s8sk8ck';
    }
}
