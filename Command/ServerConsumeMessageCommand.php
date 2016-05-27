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

/**
 * Class ServerConsumeMessageCommand
 *
 * @package Trinity\NotificationBundle\Command
 */
class ServerConsumeMessageCommand extends ContainerAwareCommand
{
    /**
     * @return int|null|void
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $this->getContainer()->get('trinity.notification.server.consumer');

        $consumer->startConsuming();
    }


    /**
     * 
     */
    protected function configure()
    {
        $this->setName('trinity:notification:server:consume');
    }
}