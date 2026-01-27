<?php

namespace SSA;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\middleware\CorsMiddleware;
use Throwable;

class Application implements RequestHandlerInterface, MiddlewareInterface
{
    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewarePipe = new MiddlewarePipe();
        $corsMiddleware = new CorsMiddleware($this->configuration['cors']);

        $middlewarePipe->pipe($this);
        $middlewarePipe->pipe($corsMiddleware);

        return $middlewarePipe->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $body = json_encode(['error' => $e->getMessage()]);
            return new Response(500, $headers, $body);
        }
    }
}