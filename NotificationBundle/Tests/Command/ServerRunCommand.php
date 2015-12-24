<?php


namespace Trinity\NotificationBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ServerRunCommand
 *
 * @package Trinity\NotificationBundle\Tests\Command
 */
class ServerRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('trinity:notification:server:run')->setDescription('Trinity server run command.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input = new ArrayInput(
            array(
                'command' => 'server:run',
                '--port' => '9000',
            )
        );

        $kernel = $this->getContainer()->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $application->run($input, $output);
    }
}