<?php


namespace Trinity\NotificationBundle\Drivers\ApiDriver;

use Trinity\FrameworkBundle\Entity\IClient;


/**
 * Interface IApiClient
 *
 * @package Trinity\NotificationBundle\Drivers\ApiDriver
 */
interface IApiClient extends IClient
{

    /** @return string */
    public function getNotificationUri ();


    /** @return string */
    public function getSecret();

}