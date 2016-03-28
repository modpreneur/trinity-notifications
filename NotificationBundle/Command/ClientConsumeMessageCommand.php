<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.03.16
 * Time: 14:41
 */

namespace Trinity\NotificationBundle\Command;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trinity\NotificationBundle\RabbitMQ\Republisher;

class ClientConsumeMessageCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $this->getContainer()->get("trinity.notification.client.consumer");
        $consumer->startConsuming();
    }


    protected function configure()
    {
        $this->setName('trinity:notification:client:message:consume');
    }

}