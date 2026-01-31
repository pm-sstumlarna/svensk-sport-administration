<?php

namespace SSA\middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware implements MiddlewareInterface
{
    private array $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('configuration')['cors'];
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Nyholm\Psr7\Response(204);
            return $this->addCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(ResponseInterface $response): Response
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->config['allow_origin'])
            ->withHeader('Access-Control-Allow-Headers', $this->config['allow_headers'])
            ->withHeader('Access-Control-Allow-Methods', $this->config['allow_methods']);
    }
}
