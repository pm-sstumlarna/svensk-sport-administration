<?php

require_once __DIR__ . '/../vendor/autoload.php';

use React\Http\HttpServer;
use React\Socket\SocketServer;
use SSA\Application;

$configuration = [];

$application = new Application($configuration);

$httpServer = new HttpServer($application->handle(...));

$socketServer = new SocketServer('0.0.0.0:8081');

$httpServer->listen($socketServer);

echo "Server running at " . $socketServer->getAddress() . PHP_EOL;

pcntl_signal(SIGTERM, function () use ($httpServer, $socketServer) {
    $socketServer->close();
    echo "Socket closed." . PHP_EOL;
    exit;
});