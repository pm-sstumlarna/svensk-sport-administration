<?php

namespace SSA\library;

use Psr\Http\Server\RequestHandlerInterface;

class Route
{
    public function __construct(
        public string $method,
        public string $path,
        public RequestHandlerInterface $handler,
        public array $parameters = [],
        public bool $isPublic = false
    ) {}

    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    public function matches(string $path): bool
    {
        $routePathParts = explode('/', trim($this->path, '/'));
        $requestPathParts = explode('/', trim($path, '/'));

        if (count($routePathParts) !== count($requestPathParts)) {
            return false;
        }

        foreach ($routePathParts as $index => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $parameterName = trim($part, '{}');
                $this->parameters[$parameterName] = $requestPathParts[$index];
            } elseif ($part !== $requestPathParts[$index]) {
                return false;
            }
        }

        return true;
    }
}
