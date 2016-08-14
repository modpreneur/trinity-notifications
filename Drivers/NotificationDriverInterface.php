<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\Drivers;

use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Interface NotificationDriverInterface.
 */
interface NotificationDriverInterface
{
    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param array                       $params
     */
    public function execute(NotificationEntityInterface $entity, ClientInterface $client, array $params = []);

    /**
     * Return name of driver.
     *
     * @return string
     */
    public function getName() : string;
}
