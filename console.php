<?php


require_once __DIR__.'/NotificationBundle/Tests/app/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;


$kernel = new AppKernel('dev', true);
$application = new Application($kernel);
$application->add(new \Trinity\NotificationBundle\Tests\Command\NotificationCommand());
$application->run();