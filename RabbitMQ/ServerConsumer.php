<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 12:18
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Message;
use Trinity\NotificationBundle\Notification\NotificationReader;

class ServerConsumer extends Consumer
{
    protected $reader;

    /**
     * ClientConsumer constructor.
     * @param BaseRabbitSetup $setup
     * @param NotificationReader $reader
     * @param ServerProducer $producer
     */
    public function __construct(BaseRabbitSetup $setup, NotificationReader $reader, ServerProducer $producer)
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

        try {
            $this->reader->read($message->content);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
