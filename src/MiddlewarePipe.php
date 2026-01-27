<?php

namespace SSA;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewarePipe implements RequestHandlerInterface
{
    private array $middlewares = [];
    private int $index = 0;

    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->index < count($this->middlewares)) {
            $middleware = $this->middlewares[$this->index];
            $this->index++;
            return $middleware->process($request, $this);
        }

        return new Response(404, [], 'End of middleware pipe');
    }
}