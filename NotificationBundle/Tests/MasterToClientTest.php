<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests;


/**
 * Class ServerToClientTest.
 */
class ServerToClientTest extends BaseTest
{
    protected $port = 8000;


    /**
     * @test
     */
    public function send()
    {
        $client = shell_exec("php console.php trinity:notification:client:run > /dev/null 2>/dev/null &");
    }

}
