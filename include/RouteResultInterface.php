<?php

namespace SSA;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteResultInterface
{
    public function getHandler(): RequestHandlerInterface;
}