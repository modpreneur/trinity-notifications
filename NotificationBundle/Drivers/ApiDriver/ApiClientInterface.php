<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers\ApiDriver;

use Trinity\FrameworkBundle\Entity\ClientInterface;


/**
 * Interface ApiClientInterface
 *
 * @package Trinity\NotificationBundle\Drivers\ApiDriver
 */
interface ApiClientInterface extends ClientInterface
{

    /** @return string */
    public function getNotificationUri();


    /** @return string */
    public function getSecret();

}