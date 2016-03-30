<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:37
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Message;
use Trinity\NotificationBundle\Notification\NotificationReader;

class ClientConsumer extends Consumer
{
    /**
     * @var NotificationReader
     */
    protected $reader;

    /**
     * ClientConsumer constructor.
     * @param BaseRabbitSetup $setup
     * @param NotificationReader $reader
     * @param ClientProducer $producer
     */
    public function __construct(BaseRabbitSetup $setup, NotificationReader $reader, ClientProducer $producer)
    {
        parent::__construct($setup, $producer);

        $this->reader = $reader;
    }


    /**
     * @inheritdoc
     */
    public function consume(Message $message)
    {
        dump($message->content);

//        if(rand(0, 100) > 50) throw new \Exception("Exception!");

        try {
            $this->reader->read($message->content);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}