<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 19.03.16
 * Time: 11:20
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Client;

class ClientSetup extends BaseRabbitSetup
{
    /**
     * @var
     */
    protected $outputExchangeName;


    /**
     * @var
     */
    protected $outputRoutingKey;


    /**
     * @var
     */
    protected $listeningQueue;

    /**
     * ClientSetup constructor.
     * @param Client $client
     * @param $outputExchangeName
     * @param $outputRoutingKey
     * @param $listeningQueue
     */
    public function __construct(Client $client, $outputExchangeName, $outputRoutingKey, $listeningQueue)
    {
        parent::__construct($client);

        $this->outputExchangeName = $outputExchangeName;
        $this->outputRoutingKey = $outputRoutingKey;
        $this->listeningQueue = $listeningQueue;
    }


    /**
     * @inheritdoc
     */
    public function getOutputExchangeName()
    {
        return $this->outputExchangeName;
    }

    /**
     * @inheritdoc
     */
    public function getOutputRoutingKey(array $data = [])
    {
        return $this->outputRoutingKey;
    }

    /**
     * @inheritdoc
     */
    public function getListeningQueue()
    {
        return $this->listeningQueue;
    }
}