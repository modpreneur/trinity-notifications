<?php

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class BeforeDeleteEntityEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class BeforeDeleteEntityEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.beforeDeleteEntity';
    
    /** @var  NotificationEntityInterface */
    protected $entity;

    /**
     * BeforeDeleteEntityEvent constructor.
     *
     * @param NotificationEntityInterface $entity
     */
    public function __construct(NotificationEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return NotificationEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param NotificationEntityInterface $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}