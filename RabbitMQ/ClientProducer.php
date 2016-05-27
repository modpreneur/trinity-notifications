<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 9:42
 */

namespace Trinity\NotificationBundle\RabbitMQ;

/**
 * Class ClientProducer
 * @package Trinity\NotificationBundle\RabbitMQ
 */
class ClientProducer extends NotificationProducer
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
    public function publish(string $data, string $exchangeName, string $clientId = null)
    {
        $this->rabbitSetup->setUp();

        $channel = $this->rabbitSetup->getChannel();

        $channel->publish(
            $data,
            [],
            $exchangeName,
            ''
        );
    }
}
