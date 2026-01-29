<?php

namespace SSA;

use Psr\Http\Message\ServerRequestInterface;
use SSA\RouteResultInterface;
use SSA\HttpException;
use SSA\RouteResult;

class Router
{

    private array $routes = [];

    public function addRoute($path, $methods, $handler): void
    {
        $this->routes[] = [$path, $methods, $handler];

    }

    public function route(ServerRequestInterface $request): ?RouteResultInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $route) {
            [$routePath, $routeMethods, $handler] = $route;
            if ($routePath === $path && in_array($method, $routeMethods)) {
                return new RouteResult($handler);
            }
        }

        throw new HttpException('Not Found', 404);
    }
}