<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 11:55
 */

namespace Trinity\NotificationBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerConsumeMessageCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $this->getContainer()->get("trinity.notification.server.consumer");

        $consumer->startConsuming();
    }


    protected function configure()
    {
        $this->setName('trinity:notification:server:message:consume');
    }
}