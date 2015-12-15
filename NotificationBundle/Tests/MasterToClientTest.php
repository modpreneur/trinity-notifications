<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Trinity\NotificationBundle\Tests\Command\ClientRunCommand;
use Trinity\NotificationBundle\Tests\Command\ServerRunCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

/**
 * Class ServerToClientTest.
 */
class ServerToClientTest extends BaseTest
{

    /**
     * @test
     */
    public function send(){

        $k = $this->createKernel();
        $application = new Application($k);

        $crc = new ClientRunCommand();
        $crc->setKernel($k);

        $application->add($crc);
        $application->add(new ServerRunCommand());

        $command = $application->find('trinity:notification:client:run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        dump($commandTester->getDisplay());
    }

}

