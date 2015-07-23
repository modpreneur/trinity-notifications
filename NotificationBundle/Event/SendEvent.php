<?php
/*
 * This file is part of the Trinity project.
 *
 */
namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;



/**
 * Class NotificationEvent
 * @author Tomáš Jančar
 *
 * @package Trinity\NotificationBundle\Event
 */
class SendEvent extends Event
{

    /** @var  Object */
    protected $entity;



    function __construct($entity)
    {
        $this->entity = $entity;
    }



    /**
     * @return Object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}