<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 15:46
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Trinity\Bundle\BunnyBundle\Consumer\CommandConsumer;

/**
 * Class MessageConsumer
 */
abstract class MessageConsumer extends CommandConsumer
{
}