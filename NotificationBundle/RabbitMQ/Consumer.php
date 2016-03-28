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
     * @var Producer
     */
    protected $producer;


    /**
     * ServerConsumer constructor.
     *
     * @param BaseRabbitSetup $setup
     * @param Producer $producer
     */
    public function __construct(BaseRabbitSetup $setup, Producer $producer)
    {
        $this->setup = $setup;
        $this->producer = $producer;
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
                $this->produceError($e, $message);
            }
        }, $this->setup->getListeningQueue(), "", false, false, false, false);

        $this->setup->getClient()->run();
    }


    public function produceError(\Exception $e, Message $message)
    {
        $message = json_decode($message->content, true);

        $data = [];
        $data["message"] = $e->getMessage();
        $data["code"] = $e->getCode();
        $data["trace"] = $e->getTraceAsString();
        $data["line"] = $e->getLine();
        $data["file"] = $e->getFile();
        $data["uid"] = $message["uid"];

        $this->producer->publishErrorMessage(json_encode($data));
    }
}