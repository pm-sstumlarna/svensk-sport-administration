<?php

namespace Test\SSA\middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\middleware\CorsMiddleware;

class CorsMiddlewareTest extends TestCase
{
    private array $corsConfig;
    protected function setUp(): void
    {
        parent::setUp();
        $this->corsConfig = [
            'allow_origin' => ['https://www.example.com'],
            'allow_methods' => ['GET, POST, PUT, DELETE'],
            'allow_headers' => ['Content-Type', 'Authorization'],
        ];
    }

    public function testAddsCorsHeadersToNormalRequest()
    {
        $middleware = new CorsMiddleware($this->corsConfig);
        $request = new ServerRequest('GET', '/test');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response(200));

        $response = $middleware->process($request, $handler);

        $this->assertStringContainsString('GET', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }

    public function testHandlesOptionsRequest()
    {
        $middleware = new CorsMiddleware($this->corsConfig);
        $request = new ServerRequest('OPTIONS', '/test');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $middleware->process($request, $handler);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('https://www.example.com', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }
}
