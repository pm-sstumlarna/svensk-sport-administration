<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class ReportHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if (str_ends_with($path, '/reports/lok-support') && $method === 'GET') {
            return $this->getLokSupportReport($storage);
        }

        if (str_ends_with($path, '/statistics') && $method === 'GET') {
            return $this->getStatistics($storage);
        }

        return new Response(404, [], 'Not Found');
    }

    private function getLokSupportReport(StorageServiceInterface $storage): ResponseInterface
    {
        $report = $storage->getLokSupportReport();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($report));
    }

    private function getStatistics(StorageServiceInterface $storage): ResponseInterface
    {
        $stats = $storage->getMemberStatistics();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($stats));
    }
}
