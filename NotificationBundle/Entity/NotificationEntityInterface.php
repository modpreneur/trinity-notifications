<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\FrameworkBundle\Entity\ClientInterface;


/**
 * Interface NotificationEntityInterface
 *
 * @package Trinity\NotificationBundle\Entity
 */
interface NotificationEntityInterface
{
    /** @return int */
    public function getId();


    /** @return ClientInterface[] */
    public function getClients();


    /**
     * @param ClientInterface $client
     * @param string $status
     * @return void
     */
    public function setNotificationStatus(ClientInterface $client, $status);

}