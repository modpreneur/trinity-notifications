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
    protected $port = 8000;

    /**
     * @test
     */
    public function send(){
        $client = exec("php console.php trinity:notification:client:run > /dev/null 2>/dev/null &");
    }

}

