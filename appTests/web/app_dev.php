<?php


require_once __DIR__.'/../app/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('dev', true, null);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
