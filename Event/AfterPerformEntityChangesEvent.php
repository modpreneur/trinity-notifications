<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:51
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class AfterPerformEntityChangesEvent
 */
class AfterPerformEntityChangesEvent extends NotificationEvent
{
    /** @var  object */
    protected $entityObject;


    /**
     * BeforePerformEntityChangesEvent constructor.
     * @param object $entityObject
     */
    public function __construct($entityObject)
    {
        $this->entityObject = $entityObject;
    }


    /**
     * @return object
     */
    public function getEntityObject()
    {
        return $this->entityObject;
    }


    /**
     * @param object $entityObject
     */
    public function setEntityObject($entityObject)
    {
        $this->entityObject = $entityObject;
    }
}
