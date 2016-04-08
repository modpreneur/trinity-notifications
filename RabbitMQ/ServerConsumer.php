<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 12:18
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Message;
use Trinity\Bundle\BunnyBundle\Consumer\Consumer;
use Trinity\Bundle\BunnyBundle\Setup\BaseRabbitSetup;
use Trinity\NotificationBundle\Notification\NotificationReader;


/**
 * Class ServerConsumer
 * @package Trinity\NotificationBundle\RabbitMQ
 */
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
