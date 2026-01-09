<?php

namespace SSA;

class Application implements RequestHandlerInterface, MiddlewareInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = new MiddlewarePipe();
        $handler->pipe($this);
        $handler->pipe(new RoutingMiddleware());
        $handler->pipe(new AuthorizationMiddleware());
        $handler->pipe(new DispatcherMiddleware());
        return $handler->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}