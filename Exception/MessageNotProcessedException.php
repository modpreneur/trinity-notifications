<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.05.16
 * Time: 13:32
 */

namespace Trinity\NotificationBundle\Exception;

use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class MessageNotProcessedException
 *
 * @package Trinity\NotificationBundle\Exception
 */
class MessageNotProcessedException extends \Exception
{
    /** @var  Message */
    protected $messageObject;


    /**
     * @return Message
     */
    public function getMessageObject()
    {
        return $this->messageObject;
    }


    /**
     * @param Message $messageObject
     *
     * @return MessageNotProcessedException
     */
    public function setMessageObject($messageObject)
    {
        $this->messageObject = $messageObject;
        return $this;
    }
}
