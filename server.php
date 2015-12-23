<?php

require './vendor/autoload.php';
use Ratchet\Server\IoServer;
use MyApp\Proxy;
error_reporting(0);
$port = 8080;
$server = IoServer::factory(new Proxy() , $port);

echo "Listening on port ". $port.PHP_EOL;

$server->run();
