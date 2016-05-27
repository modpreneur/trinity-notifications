<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\AppTests;


/**
 * Class ServerToClientTest.
 */
class ServerToClientTest extends BaseTest
{
    protected $port = 8001;


    /**
     * @test
     */
    public function send()
    {
        $client = shell_exec('php console.php trinity:notification:client:run > /dev/null 2>/dev/null &');
    }

}

