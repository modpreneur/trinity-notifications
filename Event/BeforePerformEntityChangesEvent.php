<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:51
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class BeforePerformEntityChangesEvent
 */
class BeforePerformEntityChangesEvent extends NotificationEvent
{
    /** @var  object */
    protected $entityObject;


    /** @var  array */
    protected $data;


    /** @var  array */
    protected $ignoredFields;


    /**
     * BeforePerformEntityChangesEvent constructor.
     * @param array $ignoredFields
     * @param array $data
     * @param object $entityObject
     */
    public function __construct($entityObject, array $data, array $ignoredFields)
    {
        $this->ignoredFields = $ignoredFields;
        $this->data = $data;
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


    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    /**
     * @return array
     */
    public function getIgnoredFields()
    {
        return $this->ignoredFields;
    }


    /**
     * @param array $ignoredFields
     */
    public function setIgnoredFields($ignoredFields)
    {
        $this->ignoredFields = $ignoredFields;
    }
}
