<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\Driver;

/**
 * Interface INotificationDriver.
 */
interface INotificationDriver
{
    /**
     * @param object $entity
     * @param array $params
     *
     * @return mixed
     */
    public function execute($entity, $params = []);



    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName();
}
