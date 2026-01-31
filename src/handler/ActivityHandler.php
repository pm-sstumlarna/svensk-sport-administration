<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class ActivityHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $path = $request->getUri()->getPath();
        $activityId = $route ? $route->getParameter('activityId') : null;
        $naturalPersonId = $route ? $route->getParameter('naturalPersonId') : null;

        if (str_ends_with($path, '/invitations')) {
            if ($method === 'POST') {
                return $this->createInvitation($storage, $activityId, $request);
            }
        }

        if (str_contains($path, '/invitations/') && $method === 'PATCH') {
            return $this->updateInvitation($storage, $activityId, $naturalPersonId, $request);
        }

        if (str_ends_with($path, '/attendance')) {
            if ($method === 'GET') {
                return $this->getAttendance($storage, $activityId);
            }
            if ($method === 'PUT') {
                return $this->updateAttendance($storage, $activityId, $request);
            }
        }

        switch ($method) {
            case 'GET':
                return $activityId ? $this->getActivity($storage, $activityId) : $this->listActivities($storage, $request);
            case 'POST':
                return $this->createActivity($storage, $request);
            case 'PUT':
                if ($activityId) {
                    return $this->updateActivity($storage, $activityId, $request);
                }
                return new Response(405, [], 'Method Not Allowed');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function createInvitation(StorageServiceInterface $storage, string $activityId, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true) ?: [];
        $storage->createInvitation($activityId, $data);
        return new Response(202, [], 'Invitations sent');
    }

    private function updateInvitation(StorageServiceInterface $storage, string $activityId, string $naturalPersonId, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }
        $storage->updateInvitation($activityId, $naturalPersonId, $data);
        return new Response(200, [], 'Response recorded');
    }

    private function getAttendance(StorageServiceInterface $storage, string $activityId): ResponseInterface
    {
        $attendance = $storage->getAttendance($activityId);
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($attendance));
    }

    private function updateAttendance(StorageServiceInterface $storage, string $activityId, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if ($data === null) {
            return new Response(400, [], 'Invalid JSON');
        }
        $storage->updateAttendance($activityId, $data);
        return new Response(200, [], 'Attendance registered');
    }

    private function listActivities(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $activities = $storage->listActivities();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($activities));
    }

    private function getActivity(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $activity = $storage->getActivity($id);
        if (!$activity) {
            return new Response(404, [], 'Activity Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($activity));
    }

    private function createActivity(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }
        
        $id = $storage->createActivity($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function updateActivity(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $success = $storage->updateActivity($id, $data);
        if (!$success) {
            return new Response(404, [], 'Activity Not Found');
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }
}
