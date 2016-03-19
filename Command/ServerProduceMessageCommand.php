<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.03.16
 * Time: 14:41
 */

namespace Trinity\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerProduceMessageCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->getContainer()->get("doctrine")->getRepository("NecktieAppBundle:Product")->find($input->getArgument("product"));

        $driver = $this->getContainer()->get("trinity.notification.driver.rabbit.server");

        $driver->execute($user);

        $output->writeln("message sent");
    }


    protected function configure()
    {
        $this->setName('trinity:notification:server:message:produce');

        $this->addArgument('product', InputArgument::OPTIONAL, 'Product id?');
    }
}