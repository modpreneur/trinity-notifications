<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:37
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Message;
use Trinity\Bundle\BunnyBundle\Consumer\Consumer;
use Trinity\Bundle\BunnyBundle\Setup\BaseRabbitSetup;
use Trinity\NotificationBundle\Notification\NotificationReader;


/**
 * Class ClientConsumer
 * @package Trinity\NotificationBundle\RabbitMQ
 */
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
     * Consume message
     *
     * @param Message $message
     *
     * @throws \Exception On any error
     * @return void
     */
    public function consume(Message $message)
    {
        try {
            $this->reader->read($message->content);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}