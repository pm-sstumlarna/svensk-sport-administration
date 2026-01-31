<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\AuthorizationServiceInterface;
use SSA\library\StorageServiceInterface;

class NaturalPersonHandler implements RequestHandlerInterface
{
    private AuthorizationServiceInterface $authService;

    public function __construct(AuthorizationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $path = $request->getUri()->getPath();
        $naturalPersonId = $route ? $route->getParameter('naturalPersonId') : null;

        if (str_ends_with($path, '/import') && $method === 'POST') {
            return $this->importPersons($request);
        }

        if (str_ends_with($path, '/export') && $method === 'GET') {
            return $this->exportPersons($storage, $request);
        }

        if (str_ends_with($path, '/goals')) {
            if ($method === 'GET') {
                return $this->listGoals($storage, $naturalPersonId);
            }
            if ($method === 'POST') {
                return $this->createGoal($storage, $naturalPersonId, $request);
            }
        }

        if (str_ends_with($path, '/bookings')) {
            if ($method === 'GET') {
                return $this->listBookings($storage, $naturalPersonId);
            }
        }

        switch ($method) {
            case 'GET':
                return $naturalPersonId ? $this->getPerson($storage, $naturalPersonId, $request) : $this->listPersons($storage, $request);
            case 'POST':
                return $this->createPerson($storage, $request);
            case 'PUT':
                if ($naturalPersonId) {
                    return $this->updatePerson($storage, $naturalPersonId, $request);
                }
                return new Response(405, [], 'Method Not Allowed');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function importPersons(ServerRequestInterface $request): ResponseInterface
    {
        // Mock implementation for import
        return new Response(200, [], 'Import successful');
    }

    private function exportPersons(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        // Mock implementation for export
        $csv = "id,firstName,lastName,emails,phones,addresses,roles\n";
        $persons = $storage->listNaturalPersons();
        foreach ($persons as $person) {
            $emails = array_map(fn($e) => $e['email'], $person['emails'] ?? []);
            $emailsStr = implode('; ', $emails);

            $phones = array_map(fn($p) => $p['phoneNumber'], $person['phones'] ?? []);
            $phonesStr = implode('; ', $phones);

            $addresses = array_map(function($a) {
                return sprintf("%s, %s %s, %s", $a['street'] ?? '', $a['zipCode'] ?? '', $a['city'] ?? '', $a['country'] ?? '');
            }, $person['addresses'] ?? []);
            $addressesStr = implode('; ', $addresses);

            $rolesStr = implode('; ', $person['roles'] ?? []);

            $csv .= sprintf("%s,%s,%s,\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $person['id'],
                $person['firstName'],
                $person['lastName'],
                $emailsStr,
                $phonesStr,
                $addressesStr,
                $rolesStr
            );
        }
        return new Response(200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="natural-persons.csv"'], $csv);
    }

    private function listGoals(StorageServiceInterface $storage, string $naturalPersonId): ResponseInterface
    {
        $goals = $storage->listGoals($naturalPersonId);
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($goals));
    }

    private function listBookings(StorageServiceInterface $storage, string $naturalPersonId): ResponseInterface
    {
        $person = $storage->getNaturalPerson($naturalPersonId);
        if (!$person) {
            return new Response(404, [], 'Person Not Found');
        }
        $bookings = $storage->listBookingsForPerson($naturalPersonId);
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($bookings));
    }

    private function createGoal(StorageServiceInterface $storage, string $naturalPersonId, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if ($data === null) {
            return new Response(400, [], 'Invalid JSON');
        }
        $id = $storage->createGoal($naturalPersonId, $data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function listPersons(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $natural_persons = $storage->listNaturalPersons();
        $hasU2MF = $this->authService->hasAcrLevel($request, 'U2MF');

        if (!$hasU2MF) {
            $natural_persons = array_map(function ($person) {
                unset($person['personalIdentityNumber']);
                return $person;
            }, $natural_persons);
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($natural_persons));
    }

    private function getPerson(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $person = $storage->getNaturalPerson($id);
        if (!$person) {
            return new Response(404, [], 'Person Not Found');
        }

        $hasU2MF = $this->authService->hasAcrLevel($request, 'U2MF');
        if (!$hasU2MF) {
            unset($person['personalIdentityNumber']);
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($person));
    }

    private function createPerson(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $id = $storage->createNaturalPerson($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function updatePerson(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $success = $storage->updateNaturalPerson($id, $data);
        if (!$success) {
            return new Response(404, [], 'Person Not Found');
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }
}
