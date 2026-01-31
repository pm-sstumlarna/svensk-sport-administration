<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class InvoiceHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if (str_contains($path, '/fee-levels')) {
            if ($method === 'GET') {
                return $this->listFeeLevels($storage);
            }
            if ($method === 'POST') {
                return $this->createFeeLevel($storage, $request);
            }
        }

        switch ($method) {
            case 'GET':
                return $this->listInvoices($storage);
            case 'POST':
                return $this->createInvoices($storage);
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listFeeLevels(StorageServiceInterface $storage): ResponseInterface
    {
        $feeLevels = $storage->listFeeLevels();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($feeLevels));
    }

    private function createFeeLevel(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }
        $id = $storage->createFeeLevel($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function listInvoices(StorageServiceInterface $storage): ResponseInterface
    {
        $invoices = $storage->listInvoices();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($invoices));
    }

    private function createInvoices(StorageServiceInterface $storage): ResponseInterface
    {
        $storage->createInvoices();
        return new Response(201, [], 'Invoices generated');
    }
}
