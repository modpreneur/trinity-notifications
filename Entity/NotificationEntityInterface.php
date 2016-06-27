<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\Component\Core\Interfaces\ClientInterface;


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
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();
}
