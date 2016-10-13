<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\Component\Core\Interfaces\ClientInterface;

/**
 * Interface NotificationEntityInterface.
 */
interface NotificationEntityInterface
{
    /** @return int */
    public function getId();

    /** @return ClientInterface[] */
    public function getClients();

    /**
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Get id which is used in the notification
     *
     * @return int
     */
    public function getIdForNotifications();
}
