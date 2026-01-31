<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class GroupHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();

        if ($method === 'GET') {
            return $this->listGroups($storage);
        }

        return new Response(405, [], 'Method Not Allowed');
    }

    private function listGroups(StorageServiceInterface $storage): ResponseInterface
    {
        $groups = $storage->listGroups();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($groups));
    }
}
