<?php

require_once __DIR__ . '/vendor/autoload.php';

use React\Http\HttpServer;
use React\Socket\SocketServer;
use SSA\Application;
use SSA\handler\OrganizationHandler;

$configuration = [
    'cors' => [
        'allow_origin' => '*',
        'allow_headers' => 'Content-Type, Authorization',
        'allow_methods' => 'GET, POST, PUT, DELETE, OPTIONS'
    ],
    'authentication' => [],
    'routes' => [
        ['/organizations', ['GET', 'POST'], OrganizationHandler::class],
        ['/organizations/{id}', ['GET', 'PUT', 'DELETE'], OrganizationHandler::class],
    ],
    'authorization' => [],
    'storage' => []
];

$application = new Application($configuration);

$httpServer = new HttpServer($application->handle(...));

$socketServer = new SocketServer('0.0.0.0:8081');

$httpServer->listen($socketServer);

echo "Server running at " . $socketServer->getAddress() . PHP_EOL;

if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function () use ($httpServer, $socketServer) {
        $socketServer->close();
        echo "Socket closed." . PHP_EOL;
        exit;
    });
}