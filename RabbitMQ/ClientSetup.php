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
     * @param $outputExchangeName
     * @param $outputRoutingKey
     * @param $listeningQueue
     * @param $outputErrorMessagesExchangeName
     */
    public function __construct(
        Client $client,
        $listeningQueue,
        $outputErrorMessagesExchangeName,
        $outputExchangeName
//        $outputRoutingKey
    )
    {
        parent::__construct($client, $listeningQueue, $outputErrorMessagesExchangeName);

        $this->outputExchangeName = $outputExchangeName;
//        $this->outputRoutingKey = $outputRoutingKey;
        $this->listeningQueue = $listeningQueue;
        $this->outputErrorMessagesExchangeName = $outputErrorMessagesExchangeName;
    }


//    /**
//     * @inheritdoc
//     */
//    public function getOutputRoutingKey(array $data = [])
//    {
//        return $this->outputRoutingKey;
//    }
}
