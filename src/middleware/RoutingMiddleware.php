<?php

namespace SSA\middleware;

use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\RouteResultInterface;
use SSA\RouterInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    private RouterInterface $router;

    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get(RouterInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $this->router->route($request);

        if (!$routeResult) {

            $headers = ['Content-Type' => 'application/json'];
            $body = json_encode(['error' => 'Not Found']);
            return new Response(404, $headers, $body);
        }

        return $handler->handle($request->withAttribute(RouteResultInterface::class, $routeResult));
    }
}