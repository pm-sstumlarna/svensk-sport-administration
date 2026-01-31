<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class OrganizationHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $orgId = $route ? $route->getParameter('orgId') : null;
        $path = $request->getUri()->getPath();

        if ($orgId && str_contains($path, '/board')) {
            return $this->handleBoard($storage, $orgId, $request);
        }

        if ($orgId && str_contains($path, '/members')) {
            return $this->handleMembers($storage, $orgId, $request);
        }

        switch ($method) {
            case 'GET':
                return $orgId ? $this->getOrganization($storage, $orgId) : $this->listOrganizations($storage);
            case 'POST':
                return $this->createOrganization($storage, $request);
            case 'PUT':
                if ($orgId) {
                    return $this->updateOrganization($storage, $orgId, $request);
                }
                return new Response(400, [], 'Organization ID required');
            case 'DELETE':
                if ($orgId) {
                    return $this->handleBoard($storage, $orgId, $request);
                }
                return new Response(400, [], 'Organization ID required');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function handleBoard(StorageServiceInterface $storage, string $orgId, ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        switch ($method) {
            case 'GET':
                $members = $storage->listBoardMembers($orgId);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode($members));
            case 'POST':
                $data = json_decode((string)$request->getBody(), true);
                if (!isset($data['naturalPersonId']) || !isset($data['role'])) {
                    return new Response(400, [], 'naturalPersonId and role required');
                }
                $storage->addBoardMember($orgId, $data['naturalPersonId'], $data['role']);
                return new Response(201, ['Content-Type' => 'application/json'], json_encode(['status' => 'added']));
            case 'PUT':
                $data = json_decode((string)$request->getBody(), true);
                $storage->updateBoard($orgId, $data);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
            case 'DELETE':
                $route = $request->getAttribute('route');
                $naturalPersonId = $route ? $route->getParameter('naturalPersonId') : null;
                if (!$naturalPersonId) {
                    // Try to get naturalPersonId from query param or body if not in route
                    $data = json_decode((string)$request->getBody(), true);
                    $naturalPersonId = $data['naturalPersonId'] ?? null;
                }
                if (!$naturalPersonId) {
                    return new Response(400, [], 'naturalPersonId required');
                }
                $storage->removeBoardMember($orgId, $naturalPersonId);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'removed']));
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function handleMembers(StorageServiceInterface $storage, string $orgId, ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $memberId = $route ? $route->getParameter('memberId') : null;

        switch ($method) {
            case 'GET':
                if ($memberId) {
                    $member = $storage->getMember($orgId, $memberId);
                    if (!$member) return new Response(404, [], 'Member Not Found');
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode($member));
                }
                $members = $storage->listMembers($orgId);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode($members));
            case 'POST':
                $data = json_decode((string)$request->getBody(), true);
                $id = $storage->addMember($orgId, $data);
                return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
            case 'PUT':
                if (!$memberId) return new Response(400, [], 'Member ID required');
                $data = json_decode((string)$request->getBody(), true);
                $storage->updateMember($orgId, $memberId, $data);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
            case 'DELETE':
                if (!$memberId) return new Response(400, [], 'Member ID required');
                $storage->removeMember($orgId, $memberId);
                return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'removed']));
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listOrganizations(StorageServiceInterface $storage): ResponseInterface
    {
        $orgs = $storage->listOrganizations();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($orgs));
    }

    private function getOrganization(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $org = $storage->getOrganization($id);
        if (!$org) {
            return new Response(404, [], 'Organization Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($org));
    }

    private function createOrganization(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $id = $storage->createOrganization($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function updateOrganization(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $success = $storage->updateOrganization($id, $data);
        if (!$success) {
            return new Response(500, [], 'Failed to update organization');
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }
}
