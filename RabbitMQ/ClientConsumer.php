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
class ClientConsumer extends NotificationConsumer
{

}