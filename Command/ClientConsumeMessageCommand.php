<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.03.16
 * Time: 14:41
 */

namespace Trinity\NotificationBundle\Command;

use Trinity\NotificationBundle\RabbitMQ\MessageConsumer;

/**
 * Class ClientConsumeMessageCommand
 *
 * @package Trinity\NotificationBundle\Command
 */
class ClientConsumeMessageCommand extends BaseConsumerCommand
{
    /**
     * @return MessageConsumer
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function getConsumer() : MessageConsumer
    {
        return $this->getContainer()->get('trinity.notification.client.consumer');
    }

    public function configure()
    {
        parent::configure();

        $this->setName('trinity:notification:client:consume');
    }
}
