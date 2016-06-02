<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 11:55
 */

namespace Trinity\NotificationBundle\Command;

use Trinity\NotificationBundle\RabbitMQ\MessageConsumer;

/**
 * Class ServerConsumeMessageCommand
 *
 * @package Trinity\NotificationBundle\Command
 */
class ServerConsumeMessageCommand extends BaseConsumerCommand
{
    /**
     * @return MessageConsumer
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function getConsumer() : MessageConsumer
    {
        return $this->getContainer()->get('trinity.notification.server.consumer');
    }

    public function configure()
    {
        parent::configure();

        $this->setName('trinity:notification:server:consume');
    }
}