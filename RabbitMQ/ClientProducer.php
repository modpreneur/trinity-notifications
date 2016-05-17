<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 9:42
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Trinity\Bundle\BunnyBundle\Producer\Producer;

/**
 * Class ClientProducer
 * @package Trinity\NotificationBundle\RabbitMQ
 */
class ClientProducer extends Producer
{
    /**
     * ClientProducer constructor.
     * @param ClientSetup $clientSetup
     */
    public function __construct(ClientSetup $clientSetup)
    {
        parent::__construct($clientSetup);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function publish(string $data, string $clientId = null)
    {
        $this->rabbitSetup->setUp();

        $channel = $this->rabbitSetup->getChannel();

        $channel->publish(
            $data,
            [],
            $this->rabbitSetup->getOutputExchangeName(),
            ""
        );
    }
}