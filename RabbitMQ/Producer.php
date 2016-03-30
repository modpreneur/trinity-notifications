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


    /**
     * Publish message to error message exchange
     *
     * @param string $data
     */
    public function publishErrorMessage(string $data)
    {
        $this->rabbitSetup->setUp();

        $channel = $this->rabbitSetup->getChannel();

        dump("PUBLISHING ERROR", $data);
        $channel->publish(
            $data,
            [],
            $this->rabbitSetup->getOutputErrorMessagesExchange(),
            ""
        );
    }

}