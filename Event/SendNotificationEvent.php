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

    /** @var  string */
    protected $method;

    /** @var  array */
    protected $options;

    /**
     * SendNotificationEvent constructor.
     *
     * @param NotificationEntityInterface $entity
     * @param string                      $method
     * @param array                       $options
     */
    public function __construct(NotificationEntityInterface $entity, string $method, array $options)
    {
        $this->entity = $entity;
        $this->method = $method;
        $this->options = $options;
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
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
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
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
