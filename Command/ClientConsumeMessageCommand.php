<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.03.16
 * Time: 14:41
 */

namespace Trinity\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClientConsumeMessageCommand
 *
 * @package Trinity\NotificationBundle\Command
 */
class ClientConsumeMessageCommand extends ContainerAwareCommand
{
    /**
     * @return int|null|void
     *
     * @throws \Exception
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $this->getContainer()->get('trinity.notification.client.consumer');
        $consumer->startConsuming("client_3", 2);
    }


    /**
     *
     */
    protected function configure()
    {
        $this->setName('trinity:notification:client:consume');
    }
}
