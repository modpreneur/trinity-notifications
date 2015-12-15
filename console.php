<?php


require_once __DIR__.'/NotificationBundle/Tests/app/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;

$port = null;

if(isset($argv[1])){
    if($argv[1] == 'trinity:notification:client:run'){
        $port = 8000;
    }elseif($argv[1] == 'trinity:notification:server:run'){
        $port = 9000;
    }
}

$kernel = new AppKernel('dev', true);
$kernel->setPort($port);

$application = new Application($kernel);
$application->add(new \Trinity\NotificationBundle\Tests\Command\NotificationCommand());
$application->add(new \Trinity\NotificationBundle\Tests\Command\ClientRunCommand());
$application->add(new \Trinity\NotificationBundle\Tests\Command\ServerRunCommand());

$application->run();

