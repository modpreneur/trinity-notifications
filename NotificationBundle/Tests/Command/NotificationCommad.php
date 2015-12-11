<?php


namespace Trinity\NotificationBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Trinity\NotificationBundle\Tests\Sandbox\Entity\Client;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Product;


class NotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('trinity:notification:send')
            ->setDescription('Necktie client command.')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                null
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
        $id = $input->getOption('id');

        $p = new Product();
        $c = new Client();

        if($id){
            $p->setId($id);
        }

        $p->setName("ddff");
        $p->setClient($c);
        $api = $this->getContainer()->get('trinity.notification.driver.api');
        $api->execute($p, $c);
        $output->writeln('Notification was sent.');
    }
}