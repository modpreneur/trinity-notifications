<?php


namespace Trinity\NotificationBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class MasterRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('trinity:notification:master:run')
            ->setDescription('Trinity master run command.');
        ;
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input = new ArrayInput(array(
            'command' => 'server:run',
            '--port' => '9000'
        ));

        $kernel = $this->getContainer()->get('kernel');
        $kernel->setPort(9000);

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $application->run($input, $output);
    }
}