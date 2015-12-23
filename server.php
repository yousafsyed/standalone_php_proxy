<?php

require './vendor/autoload.php';
use Ratchet\Server\IoServer;
use MyApp\Proxy;

$port = 8080;
$server = IoServer::factory(new Proxy() , $port);

echo "Listening on port ". $port;

$server->run();
