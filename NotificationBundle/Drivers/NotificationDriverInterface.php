<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\Driver;

use Trinity\FrameworkBundle\Entity\ClientInterface;


/**
 * Interface NotificationDriverInterface.
 */
interface NotificationDriverInterface
{
    /**
     * @param object $entity
     * @param ClientInterface $client
     * @param array $params
     *
     * @return mixed
     */
    public function execute($entity, ClientInterface $client, $params = []);


    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName();
}
