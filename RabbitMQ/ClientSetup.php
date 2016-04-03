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
     * @var string
     */
    protected $outputExchangeName;


    /**
     * @var string
     */
    protected $outputRoutingKey;


    /**
     * @var string
     */
    protected $listeningQueue;

    /**
     * @var string
     */
    protected $outputErrorMessagesExchangeName;


    /**
     * ClientSetup constructor.
     * @param Client $client
     * @param $listeningQueue
     * @param $outputErrorMessagesExchangeName
     * @param $outputExchangeName
     * @internal param $outputRoutingKey
     */
    public function __construct(
        Client $client,
        $listeningQueue,
        $outputErrorMessagesExchangeName,
        $outputExchangeName
    )
    {
        parent::__construct($client, $listeningQueue, $outputErrorMessagesExchangeName);

        $this->outputExchangeName = $outputExchangeName;
        $this->listeningQueue = $listeningQueue;
        $this->outputErrorMessagesExchangeName = $outputErrorMessagesExchangeName;
    }

    /**
     * @inheritdoc
     */
    public function getOutputExchangeName()
    {
        return $this->outputExchangeName;
    }
}
