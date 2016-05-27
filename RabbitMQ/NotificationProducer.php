<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.05.16
 * Time: 12:13
 */

namespace Trinity\NotificationBundle\RabbitMQ;

use Trinity\Bundle\BunnyBundle\Producer\Producer;

/**
 * Class NotificationProducer
 *
 * @package Trinity\NotificationBundle\RabbitMQ
 */
abstract class NotificationProducer extends Producer
{
    /** @var  NotificationSetup */
    protected $rabbitSetup;

    /**
     * @return NotificationSetup
     */
    public function getRabbitSetup() : NotificationSetup
    {
        return $this->rabbitSetup;
    }
}
