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
    /** @return ClientInterface[] */
    public function getClients();

}