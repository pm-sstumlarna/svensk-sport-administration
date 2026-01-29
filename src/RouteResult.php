<?php

namespace SSA;

use Psr\Http\Server\RequestHandlerInterface;
use SSA\RouteResultInterface;

class RouteResult implements RouteResultInterface
{

    private RequestHandlerInterface $handler;

    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }
}