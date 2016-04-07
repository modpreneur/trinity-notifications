<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 9:42
 */

namespace Trinity\NotificationBundle\RabbitMQ;


class ServerProducer extends Producer
{
    public function __construct(ServerSetup $serverSetup)
    {
        parent::__construct($serverSetup);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function publish(string $data, string $clientId = null)
    {
        $this->rabbitSetup->setUp();

        $channel = $this->rabbitSetup->getChannel();

        //routingKey = queueName in this case
        $routingKey = $this->rabbitSetup->getOutputRoutingKey(["clientId" => $clientId]);

        /** @var ServerSetup $setup */
        $setup = $this->rabbitSetup;

        $setup->declareServerToClientQueue($routingKey);
        $setup->bindServerToClientQueueToExchange($routingKey);

        $channel->publish(
            $data,
            [],
            $this->rabbitSetup->getOutputExchangeName(),
            $this->rabbitSetup->getOutputRoutingKey(["clientId" => $clientId])
        );
    }
}