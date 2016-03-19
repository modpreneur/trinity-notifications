<?php


namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Channel;
use Bunny\Client;

/**
 * Class Producer
 * @package Trinity\NotificationBundle\RabbitMQ
 */
abstract class Producer
{
    /**
     * @var BaseRabbitSetup
     */
    protected $rabbitSetup;


    public function __construct(BaseRabbitSetup $rabbitSetup)
    {
        $this->rabbitSetup = $rabbitSetup;
    }

    /**
     * Publish data to the queue.
     *
     * @param string $data
     * @param string $clientId
     */
    abstract public function publish(string $data, string $clientId = null);

}