<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.05.16
 * Time: 10:44
 */

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Entity\StatusMessage;

/**
 * Class StatusMessageEvent
 *
 * @package Trinity\NotificationBundle\Event
 */
class StatusMessageEvent extends NotificationEvent
{
    /** @var  StatusMessage */
    protected $statusMessage;


    /**
     * StatusMessageEvent constructor.
     *
     * @param StatusMessage $statusMessage
     */
    public function __construct(StatusMessage $statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }


    /**
     * @return StatusMessage
     */
    public function getStatusMessage() : StatusMessage
    {
        return $this->statusMessage;
    }


    /**
     * @param StatusMessage $statusMessage
     */
    public function setStatusMessage(StatusMessage $statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }
}
