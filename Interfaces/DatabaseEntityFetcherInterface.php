<?php

namespace Trinity\NotificationBundle\Interfaces;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Interface DatabaseEntityFetcherInterface
 *
 * Responsible for fetching an entity from a database based on a notification data.
 * This data is an associative array, which will cause headaches but I'm not aware of any better solution.
 *
 * @package Trinity\NotificationBundle\Interfaces
 */
interface DatabaseEntityFetcherInterface
{
    /**
     * Fetch an entity from the database.
     *
     * @param string $fullClassName FQCN of the entity.
     * @param array $notificationData Associative array of data.
     *          For keys, refer to the entity's Source annotation on the sender system.
     *
     * @return null|NotificationEntityInterface
     */
    public function fetchEntity(string $fullClassName, array $notificationData): ?NotificationEntityInterface;
}