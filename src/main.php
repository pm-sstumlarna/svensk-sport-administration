<?php

namespace SSA;

$application = new Application();

$httpServer = new HttpServer($application->handle(...));

$socketServer = new SocketServer('0.0.0.0:80');

$httpServer->listen($socketServer);

echo "Server running at " . $socketServer->getAddress() . PHP_EOL;

pcntl_signal(SIGTERM, function () use ($httpServer, $socketServer) {
    $httpServer->shutdown();
    echo "Server stopped." . PHP_EOL;
    $socketServer->close();
    echo "Socket closed." . PHP_EOL;
    exit;
});