<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.03.16
 * Time: 20:15
 */

namespace Trinity\NotificationBundle\Notification;


use Symfony\Component\DependencyInjection\Container;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;

class ServerNotificationReader extends NotificationReader
{
    /**
     * @var ClientSecretProviderInterface
     */
    protected $clientSecretProvider;


    /**
     * @param ClientSecretProviderInterface $clientSecretProvider
     */
    public function setClientSecretProvider(ClientSecretProviderInterface $clientSecretProvider)
    {
        $this->clientSecretProvider = $clientSecretProvider;
    }


    /**
     * @inheritdoc
     */
    public function getClientSecret(NotificationBatch $batch = null)
    {
        return $this->clientSecretProvider->getClientSecret($batch->getClientId());
    }


}