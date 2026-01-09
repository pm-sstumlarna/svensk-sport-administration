<?php

namespace App\Middleware;

class RoutingMiddleware implements MiddlewareInterface
{
    private RouterInterface $router;

    public function __construct()
    {
        // Initialization code if needed
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->router->match($request)) {
            // Route matched, proceed to the next middleware
            return $handler->handle($request);
        }

        return new Response(404, [], 'Not Found');
    }
}