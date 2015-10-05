<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\Driver;

use Trinity\FrameworkBundle\Entity\IClient;


/**
 * Interface INotificationDriver.
 */
interface INotificationDriver
{
    /**
     * @param object $entity
     * @param IClient $client
     * @param array $params
     *
     * @return mixed
     */
    public function execute($entity, $client, $params = []);


    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName();
}
