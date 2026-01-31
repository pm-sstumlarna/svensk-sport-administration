<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class BookingHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $bookingId = $route ? $route->getParameter('bookingId') : null;

        switch ($method) {
            case 'GET':
                return $bookingId ? $this->getBooking($storage, $bookingId) : $this->listBookings($storage, $request);
            case 'POST':
                return $this->createBooking($storage, $request);
            case 'PATCH':
                if ($bookingId) {
                    return $this->updateBookingStatus($storage, $bookingId, $request);
                }
                return new Response(405, [], 'Method Not Allowed');
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listBookings(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $bookings = $storage->listBookings();
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($bookings));
    }

    private function getBooking(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $booking = $storage->getBooking($id);
        if (!$booking) {
            return new Response(404, [], 'Booking Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($booking));
    }

    private function createBooking(StorageServiceInterface $storage, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }

        $isMinor = true;
        // Validate mandatory child personnummer
        if (isset($data['naturalPersonId'])) {
            $person = $storage->getNaturalPerson($data['naturalPersonId']);
            if (!$person) {
                return new Response(404, [], 'Person Not Found');
            }
            if (empty($person['personalIdentityNumber'])) {
                return new Response(400, [], 'Child must have a personal identity number for swim course booking');
            }
            $isMinor = $this->isMinor($person['personalIdentityNumber']);
        } else {
            return new Response(400, [], 'naturalPersonId is required');
        }

        // Validate guardian information if minor
        if ($isMinor) {
            if (empty($data['guardianName'])) {
                return new Response(400, [], 'guardianName is required');
            }
            if (empty($data['guardianEmail']) && empty($data['guardianPhone'])) {
                return new Response(400, [], 'At least one contact detail (email or phone) for the guardian is required');
            }
        }

        $id = $storage->createBooking($data);
        $booking = $storage->getBooking($id);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode($booking));
    }

    private function isMinor(string $personalIdentityNumber): bool
    {
        // Simple Swedish personal identity number parsing (YYYYMMDD-XXXX or YYMMDD-XXXX)
        // For simplicity, let's assume it's YYYYMMDD-XXXX or YYYYMMDDXXXX
        $pin = str_replace('-', '', $personalIdentityNumber);
        if (strlen($pin) === 12) {
            $year = (int)substr($pin, 0, 4);
            $month = (int)substr($pin, 4, 2);
            $day = (int)substr($pin, 6, 2);
        } elseif (strlen($pin) === 10) {
            $year = (int)substr($pin, 0, 2);
            // Handling the century for 10-digit PIN is tricky, but usually + means > 100 years.
            // Let's assume 10-digit is 1900s for now, or use a better heuristic if needed.
            $year += ($year < date('y')) ? 2000 : 1900;
            $month = (int)substr($pin, 2, 2);
            $day = (int)substr($pin, 4, 2);
        } else {
            return true; // Default to minor if unknown format to be safe
        }

        $birthDate = new \DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day));
        $now = new \DateTime();
        $age = $now->diff($birthDate)->y;

        return $age < 18;
    }

    private function updateBookingStatus(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!isset($data['status'])) {
            return new Response(400, [], 'status is required');
        }

        $success = $storage->updateBookingStatus($id, $data['status']);
        if (!$success) {
            return new Response(404, [], 'Booking Not Found');
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }
}
