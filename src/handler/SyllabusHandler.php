<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class SyllabusHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $syllabusId = $route ? $route->getParameter('syllabusId') : null;
        $orgId = $route ? $route->getParameter('orgId') : null;

        switch ($method) {
            case 'GET':
                return $syllabusId ? $this->getSyllabus($storage, $syllabusId) : $this->listSyllabuses($storage, $request);
            case 'POST':
                return $this->createSyllabus($storage, $request);
            case 'PUT':
                if ($syllabusId) {
                    return $this->updateSyllabus($storage, $syllabusId, $request);
                }
                return new Response(405, [], 'Method Not Allowed');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listSyllabuses(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $syllabuses = $storage->listSyllabuses();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($syllabuses));
    }

    private function getSyllabus(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $syllabus = $storage->getSyllabus($id);
        if (!$syllabus) {
            return new Response(404, [], 'Syllabus Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($syllabus));
    }

    private function createSyllabus(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $id = $storage->createSyllabus($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function updateSyllabus(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $success = $storage->updateSyllabus($id, $data);
        if (!$success) {
            return new Response(404, [], 'Syllabus Not Found');
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }
}
