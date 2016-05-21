<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 15:58
 */

namespace Trinity\NotificationBundle\Exception;


/**
 * Class AssociationEntityNotFoundException
 */
class AssociationEntityNotFoundException extends NotificationException
{
//todo: remove constructor!!! in all exceptions
    /** @var  string */
    protected $messageId;

    /** @var  string */
    protected $entityName;

    /** @var  string */
    protected $entityId;

    /**
     * AssociationEntityNotFoundException constructor.
     *
     * @param string $messageId
     * @param string $entityName
     * @param string $entityId
     */
    public function __construct(string $messageId = null, string $entityName = null, string $entityId = null)
    {
        $this->messageId = $messageId;
        $this->entityName = $entityName;
        $this->entityId = $entityId;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }


    /**
     * @param string $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }


    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }


    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }


    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }


    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
}
