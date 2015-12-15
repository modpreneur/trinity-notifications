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

        //exec("php console.php trinity:notification:client:run");

    }

}

