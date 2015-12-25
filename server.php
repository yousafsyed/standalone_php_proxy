<?php
require './vendor/autoload.php';
use Ratchet\Server\IoServer;
use BrokerGenius\Proxy;
error_reporting(0);
$port = 8888;
$server = IoServer::factory(new Proxy() , $port);
echo "\033[32mListening on port ". $port.PHP_EOL."\033[0m";

$server->run();
