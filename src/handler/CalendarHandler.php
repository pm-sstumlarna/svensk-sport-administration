<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class CalendarHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $activities = $storage->listActivities();
        $icsContent = $this->generateIcs($activities);

        return new Response(200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="calendar.ics"'
        ], $icsContent);
    }

    private function generateIcs(array $activities): string
    {
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//SSA//Svensk Sport Administration//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";

        foreach ($activities as $activity) {
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:" . ($activity['id'] ?? uniqid()) . "@ssa.example.com\r\n";
            $ics .= "DTSTAMP:" . $this->formatDate(new \DateTime()) . "\r\n";
            $ics .= "DTSTART:" . $this->formatDate(new \DateTime($activity['startTime'])) . "\r\n";
            $ics .= "DTEND:" . $this->formatDate(new \DateTime($activity['endTime'])) . "\r\n";
            $ics .= "SUMMARY:" . $this->escapeString($activity['title'] ?? 'Activity') . "\r\n";
            if (!empty($activity['description'])) {
                $ics .= "DESCRIPTION:" . $this->escapeString($activity['description']) . "\r\n";
            }
            if (!empty($activity['location'])) {
                $ics .= "LOCATION:" . $this->escapeString($activity['location']) . "\r\n";
            }
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    private function formatDate(\DateTime $date): string
    {
        return $date->format('Ymd\THis\Z');
    }

    private function escapeString(string $string): string
    {
        return str_replace([",", ";", "\n"], ["\\,", "\\;", "\\n"], $string);
    }
}
