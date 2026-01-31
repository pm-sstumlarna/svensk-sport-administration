<?php

namespace Test\SSA\integration;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\Application;
use SSA\handler\OrganizationHandler;
use SSA\library\StorageServiceInterface;
use SSA\Router;
use SSA\RouteResult;
use SSA\RouteResultInterface;
use SSA\RouterInterface;

class MiddlewareExecutionTest extends TestCase
{
    public function testMiddlewareExecution()
    {
        $appConfig = [
            'cors' => [
                'allow_origin' => '*',
                'allow_headers' => 'Content-Type, Authorization',
                'allow_methods' => 'GET, POST, PUT, DELETE, OPTIONS'
            ],
            'authentication' => [],
            'routes' => [
                ['/organizations', ['GET', 'POST'], OrganizationHandler::class],
                ['/organizations/{id}', ['GET', 'PUT', 'DELETE'], OrganizationHandler::class]
            ],
            'authorization' => [],
            'storage' => [],
        ];
        $router = $this->createStub(RouterInterface::class);
        $routeResult = $this->createStub(RouteResultInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $router->method('route')->willReturn($routeResult);
        $routeResult->method('getHandler')->willReturn($handler);

        $request = new ServerRequest('GET', '/organizations');

        $app = new Application($appConfig);
        $app->getContainer()->set(RouterInterface::class, $router);

        $storage = $app->getContainer()->get(StorageServiceInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ServerRequestInterface $request) use ($storage) {
                return $request->getAttribute(StorageServiceInterface::class) === $storage;
            }))
            ->willReturn(new Response(200));

        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
