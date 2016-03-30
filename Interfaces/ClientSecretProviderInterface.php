<?php

namespace Trinity\NotificationBundle\Interfaces;

/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.03.16
 * Time: 20:17
 */
interface ClientSecretProviderInterface
{
    /**
     * @param string $clientId
     *
     * @return string Client secret
     */
    public function getClientSecret(string $clientId);
}