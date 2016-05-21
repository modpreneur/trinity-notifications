<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 18:20
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ChangesDoneEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class ChangesDoneEvent extends Event
{
    /**
     * @var array
     */
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
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }
}