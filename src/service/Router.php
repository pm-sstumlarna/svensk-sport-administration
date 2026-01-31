<?php

namespace SSA\service;

use Psr\Http\Message\ServerRequestInterface;
use SSA\library\Route;
use SSA\library\RouterInterface;

class Router implements RouterInterface
{
    /** @var Route[] */
    private array $routes = [];

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function match(ServerRequestInterface $request): ?Route
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $route) {
            if ($route->method === $method && $this->matchPath($route->path, $path, $parameters)) {
                return new Route($route->method, $route->path, $route->handler, $parameters, $route->isPublic);
            }
        }

        return null;
    }

    private function matchPath(string $routePath, string $requestPath, &$parameters): bool
    {
        $parameters = [];
        $routePathParts = explode('/', trim($routePath, '/'));
        $requestPathParts = explode('/', trim($requestPath, '/'));

        if (count($routePathParts) !== count($requestPathParts)) {
            return false;
        }

        foreach ($routePathParts as $index => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $parameterName = trim($part, '{}');
                $parameters[$parameterName] = $requestPathParts[$index];
            } elseif ($part !== $requestPathParts[$index]) {
                return false;
            }
        }

        return true;
    }
}
