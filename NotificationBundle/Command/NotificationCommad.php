<?php


namespace Trinity\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Trinity\NotificationBundle\Tests\Entity\Client;
use Trinity\NotificationBundle\Tests\Entity\Product;


class NotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('trinity:notification:send')
            ->setDescription('Necktie client command.')
            ->addArgument(
                'id',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'entity',
                InputArgument::REQUIRED
            )
            ->addOption(
                'method',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                'PUT'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $p = new Product();
        $c = new Client();


        $api = $this->getContainer()->get('trinity.notification.driver.api');
        dump($api->execute($p, $c));


        $output->writeln('Notification was sent.');
    }
}