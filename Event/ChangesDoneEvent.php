<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 18:20
 */

namespace Trinity\NotificationBundle\Event;

/**
 * Class ChangesDoneEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class ChangesDoneEvent extends NotificationEvent
{
    /** @var array */
    protected $entities;


    /**
     * ChangesDoneEvent constructor.
     *
     * @param array $entities
     */
    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }


    /**
     * @return array
     */
    public function getEntities() : array
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;
    }
}