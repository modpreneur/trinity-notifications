<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;


/**
 * Class NotificationEvent.
 *
 * @author Tomáš Jančar
 */
class SendEvent extends Event
{
    /** @var  object */
    protected $entity;


    /**
     * @param object $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }


    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
