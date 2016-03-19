<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 19.03.16
 * Time: 13:10
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Channel;
use Bunny\Message;

abstract class Consumer
{
    /**
     * @var BaseRabbitSetup
     */
    protected $setup;


    /**
     * ServerConsumer constructor.
     *
     * @param BaseRabbitSetup $setup
     */
    public function __construct(BaseRabbitSetup $setup)
    {
        $this->setup = $setup;
    }


    /**
     * Start reading messages from rabbit.
     */
    public function startConsuming()
    {
        $this->setup->setUp();

        $channel = $this->setup->getChannel();

        $channel->consume(function (Message $message, Channel $channel) {
            try {
                $this->consume($message);
                $channel->ack($message);
            } catch (\Exception $e) {
                $channel->nack($message, false, false); //discard the message
            }
        }, $this->setup->getListeningQueue(), "", false, false, false, false);

        $this->setup->getClient()->run();
    }


    /**
     * Consume message
     *
     * @param Message $message
     *
     * @throws \Exception On any error
     * @return void
     */
    abstract public function consume(Message $message);
}