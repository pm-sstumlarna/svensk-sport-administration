<?php

namespace SSA\middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class StorageMiddleware implements MiddlewareInterface
{
    private StorageServiceInterface $storage;

    public function __construct(ContainerInterface $container)
    {
        $this->storage = $container->get(StorageServiceInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(StorageServiceInterface::class, $this->storage));
    }
}