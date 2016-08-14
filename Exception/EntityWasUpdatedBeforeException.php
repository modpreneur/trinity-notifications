<?php

namespace Trinity\NotificationBundle\Exception;

/**
 * Class EntityWasUpdatedBeforeException.
 *
 * The exception in thrown when the entity's property updatedAt is bigger than message createdAt.
 * This covers the situation when the entity on the receiving system has been edited but the sending
 * system has not registered the change
 */
class EntityWasUpdatedBeforeException extends \Exception
{
    protected $entity;

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
