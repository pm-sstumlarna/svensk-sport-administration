<?php

namespace SSA;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface, MiddlewareInterface
{
    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $content = [
            'message' => 'Svensk Sport Administration är under utveckling',
            'version' => '0.0.1',
            'config' => $this->configuration,
        ];

        $headers = ['Content-Type' => 'application/json'];
        $body = json_encode($content);

        return new Response(200, $headers, $body);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}