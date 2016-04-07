<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 19.03.16
 * Time: 11:17
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Channel;
use Bunny\Client;

abstract class BaseRabbitSetup
{
    /**
     * @var Client
     */
    protected $client;


    /**
     * @var Channel
     */
    protected $channel;


    /**
     * @var string
     */
    protected $listeningQueue;

    /**
     * @var string
     */
    protected $outputErrorMessagesExchangeName;


    /**
     * BaseRabbitSetup constructor.
     * @param Client $client
     * @param string $listeningQueue
     * @param string $outputErrorMessagesExchangeName
     */
    public function __construct(Client $client, string $listeningQueue, string $outputErrorMessagesExchangeName)
    {
        $this->client = $client;
        $this->listeningQueue = $listeningQueue;
        $this->outputErrorMessagesExchangeName = $outputErrorMessagesExchangeName;
    }


    /**
     * Set up the rabbit queue, exchanges and so.
     * @throws \Exception
     */
    public function setUp()
    {
        $this->createChannel();
    }


    /**
     * Get name of the queue which will be listening to.
     *
     * @return string
     */
    public function getListeningQueue()
    {
        return $this->listeningQueue;
    }


    /**
     * Get name of the exchange which will be used to produce error messages.
     *
     * @return string
     */
    public function getOutputErrorMessagesExchange()
    {
        return $this->outputErrorMessagesExchangeName;
    }


    /**
     * Create channel to the rabbit.
     *
     * @throws \Exception
     */
    protected function createChannel()
    {
        if (null === $this->channel) {

            $this->client->connect();
            $this->channel = $this->client->channel();
        }
    }


    /**
     * Get channel which will be used for publishing/listening messages.
     *
     * @return \Bunny\Channel
     * @throws \Exception
     */
    public function getChannel()
    {
        $this->createChannel();

        return $this->channel;
    }


    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }


    /**
     * Disconnect from the rabbit server.
     */
    public function disconnect()
    {
        $this->client->disconnect();
    }


    /**
     * Get exchange name which will be used to produce messages
     *
     * @return string
     */
    abstract public function getOutputExchangeName();

}