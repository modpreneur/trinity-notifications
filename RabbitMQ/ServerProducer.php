<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 9:42
 */

namespace Trinity\NotificationBundle\RabbitMQ;

/**
 * Class ServerProducer
 *
 * @package Trinity\NotificationBundle\RabbitMQ
 */
class ServerProducer extends NotificationProducer
{
    /**
     * ServerProducer constructor.
     *
     * @param ServerSetup $serverSetup
     */
    public function __construct(ServerSetup $serverSetup)
    {
        parent::__construct($serverSetup);
    }


    /**
     * @inheritdoc
     *
     * @param clientId string Client id
     *
     * @throws \Exception
     */
    public function publish(string $data, string $exchangeName, string $clientId = null)
    {
        /** @var ServerSetup $setup */
        $setup = $this->rabbitSetup;
        $setup->setUp();

        $channel = $setup->getChannel();

        //routingKey = queueName in this case
        $routingKey = $setup->getOutputRoutingKey(['clientId' => $clientId]);

        $setup->declareServerToClientQueue($routingKey);
        $setup->bindServerToClientQueueToExchange($routingKey);

        $channel->publish(
            $data,
            [],
            $exchangeName,
            $setup->getOutputRoutingKey(['clientId' => $clientId])
        );
    }
}