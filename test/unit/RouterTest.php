<?php

namespace Test\SSA\unit;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\HttpException;
use SSA\Router;
use PHPUnit\Framework\TestCase;
use SSA\RouteResultInterface;

class RouterTest extends TestCase
{
    public function testNonExistingRoute()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');
        $this->expectExceptionCode(404);

        $router = new Router();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $uri = $this->createStub(UriInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $uri->method('getPath')->willReturn('/invalid');

        $router->addRoute('/test', ['GET'], $handler);
        $router->route($request);
    }

    public function testAddRouteAndValidateRouteResult()
    {
        $router = new Router();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $uri = $this->createStub(UriInterface::class);

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);
        $uri->method('getPath')->willReturn('/test');

        $router->addRoute('/test', ['GET'], $handler);
        try {
            $result = $router->route($request);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        $this->assertInstanceOf(RouteResultInterface::class, $result);
        $this->assertSame($handler, $result->getHandler());
    }


}
