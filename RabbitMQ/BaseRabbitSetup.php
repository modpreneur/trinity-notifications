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
    protected $channel = null;


    /**
     * Set up the rabbit queue, exchanges and so.
     */
    public function setUp()
    {
        $this->createChannel();
    }


    /**
     * Get name of the queue which will be used to produce messages.
     *
     * @return string
     */
    abstract public function getOutputExchangeName();


    /**
     * Get routing key which will be used to route messages to queue.
     *
     * @param array $data Additional data required to generate routing key.
     *
     * @return string
     */
    abstract public function getOutputRoutingKey(array $data = []);


    /**
     * Get name of the queue which will be listening to.
     *
     * @return string
     */
    abstract public function getListeningQueue();


    public function __construct(Client $client)
    {
        $this->client = $client;
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
}