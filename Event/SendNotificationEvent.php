<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 13.06.16
 * Time: 9:45.
 */
namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class SendNotificationEvent.
 *
 * This event is dispatched when a notification should be sent.
 * Dispatching this event in EntityListener should break circular dependency on EntityManager.
 */
class SendNotificationEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.sendNotification';

    /** @var  NotificationEntityInterface */
    protected $entity;

    /** @var  array */
    protected $changeSet;

    /** @var  string */
    protected $method;

    /** @var  array */
    protected $options;

    protected $forced;

    /**
     * SendNotificationEvent constructor.
     *
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet
     * @param string                      $method
     * @param array                       $options
     * @param bool                        $forced
     */
    public function __construct(
        NotificationEntityInterface $entity,
        array $changeSet,
        string $method,
        array $options,
        bool $forced
    ) {
        $this->entity = $entity;
        $this->changeSet = $changeSet;
        $this->method = $method;
        $this->options = $options;
        $this->forced = $forced;
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

    /**
     * @return array
     */
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    /**
     * @param array $changeSet
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return bool
     */
    public function getForced(): bool
    {
        return $this->forced;
    }

    /**
     * @param bool $forced
     */
    public function setForced(bool $forced)
    {
        $this->forced = $forced;
    }
}
