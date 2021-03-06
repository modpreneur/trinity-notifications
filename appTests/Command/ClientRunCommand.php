<?php

namespace Trinity\NotificationBundle\AppTests\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClientRunCommand.
 */
class ClientRunCommand extends ContainerAwareCommand
{
    private $kernel;

    /**
     * @param mixed $kernel
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this
            ->setName('trinity:notification:client:run')
            ->setDescription('Trinity client run command.');
    }

    public function isRunning($pid)
    {
        try {
            $result = shell_exec(sprintf('ps %d', $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->kernel;
        $input = new ArrayInput(
            array(
                'command' => 'server:run',
                '--port' => '8001',
            )
        );

        if ($this->kernel === null) {
            $kernel = $this->getContainer()->get('kernel');
        }

        $application = new Application($kernel, true, 8001);
        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}
