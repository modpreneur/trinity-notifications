<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 15:58.
 */
namespace Trinity\NotificationBundle\Exception;

use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class AssociationEntityNotFoundException.
 */
class AssociationEntityNotFoundException extends NotificationException
{
    /** @var  Message */
    protected $messageObject;

    /** @var  string */
    protected $entityName;

    /** @var  string */
    protected $entityId;

    /**
     * @return Message
     */
    public function getMessageObject()
    {
        return $this->messageObject;
    }

    /**
     * @param Message $message
     */
    public function setMessageObject(Message $message)
    {
        $this->messageObject = $message;
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
    public function setEntityName( $entityName)
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
    public function setEntityId( $entityId)
    {
        $this->entityId = $entityId;
    }
}
