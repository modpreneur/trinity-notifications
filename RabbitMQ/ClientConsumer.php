<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:37
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;

class ClientConsumer extends Consumer
{
    /**
     * @inheritdoc
     */
    public function consume(Message $message)
    {
        if(rand(0, 100) > 50) throw new \Exception("Exception!");

        dump($message->content);
    }
}