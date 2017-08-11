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
     * @param bool                        $force     If the changeSet should not been compared with the database.
     * @param array                       $changeSet
     * @param array                       $params
     *
     * @return
     */
    public function execute(
        NotificationEntityInterface $entity,
        ClientInterface $client,
        bool $force,
        array $changeSet = [],
        array $params = []
    );

    /**
     * Return name of driver.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Clears the inner state of the driver
     *
     * @return void
     */
    public function clear();
}
