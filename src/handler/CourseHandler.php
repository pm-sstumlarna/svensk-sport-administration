<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class CourseHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $courseId = $route ? $route->getParameter('courseId') : null;
        $orgId = $route ? $route->getParameter('orgId') : null;

        switch ($method) {
            case 'GET':
                return $courseId ? $this->getCourse($storage, $courseId) : $this->listCourses($storage, $request);
            case 'POST':
                if ($courseId) {
                    // Specific course POST - detail update or similar
                    return new Response(200, [], 'Course action performed');
                }
                return $this->createCourse($storage, $request);
            case 'DELETE':
                if ($courseId) {
                    $storage->deleteCourse($courseId);
                    return new Response(200, [], 'Course deleted');
                }
                return new Response(400, [], 'Course ID required');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listCourses(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute('route');
        $orgId = $route ? $route->getParameter('orgId') : null;

        if ($orgId) {
            $courses = $storage->listCoursesByOrganization($orgId);
        } else {
            $courses = $storage->listCourses();
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($courses));
    }

    private function getCourse(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $course = $storage->getCourse($id);
        if (!$course) {
            return new Response(404, [], 'Course Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($course));
    }

    private function createCourse(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $id = $storage->createCourse($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }
}