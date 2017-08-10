<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 09.04.16
 * Time: 11:26.
 */
namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class DriverExecuteEvent.
 */
abstract class DriverExecuteEvent extends NotificationEvent
{
    /** @var NotificationEntityInterface */
    protected $entity;

    /**
     * DriverExecuteEvent constructor.
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
    public function setEntity(NotificationEntityInterface $entity)
    {
        $this->entity = $entity;
    }
}
