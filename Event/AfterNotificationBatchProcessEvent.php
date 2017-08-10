<?php

namespace Trinity\NotificationBundle\Event;

/**
 * Class AfterNotificationBatchProcessEvent.
 */
class AfterNotificationBatchProcessEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.afterNotificationBatchProcess';

    /** @var  string Integer representing user's id or string representing system(e.g. client_3, necktie) */
    protected $userIdentification;

    /** @var  string Client id */
    protected $clientId;

    /** @var  \Exception */
    protected $exception;

    /**
     * AfterNotificationBatchProcessEvent constructor.
     *
     * @param string     $userIdentification
     * @param string     $clientId
     * @param \Exception $exception
     */
    public function __construct( $userIdentification, $clientId, \Exception $exception = null)
    {
        $this->userIdentification = $userIdentification;
        $this->clientId = $clientId;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function getUserIdentification()
    {
        return $this->userIdentification;
    }

    /**
     * @param string $userIdentification
     */
    public function setUserIdentification( $userIdentification)
    {
        $this->userIdentification = $userIdentification;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId( $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception = null)
    {
        $this->exception = $exception;
    }
}
