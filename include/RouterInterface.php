<?php

namespace SSA;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function route(ServerRequestInterface $request): RouteResultInterface;
}