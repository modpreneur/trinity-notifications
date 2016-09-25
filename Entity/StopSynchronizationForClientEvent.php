<?php

namespace Trinity\NotificationBundle\Entity;

use Trinity\Component\Core\Interfaces\ClientInterface;
use Trinity\NotificationBundle\Event\NotificationEvent;

/**
 * Class StopSynchronizationForClientEvent
 */
class StopSynchronizationForClientEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.stop_synchronization_for_client';

    /** @var ClientInterface */
    protected $client;

    /**
     * StopSynchronizationForClientEvent constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client = null)
    {
        $this->client = $client;
    }
}