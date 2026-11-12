<?php

namespace App\Controllers;

use App\Models\Event;
use DateTimeImmutable;

class CalendarController
{
    public function __construct(private Event $events)
    {
    }

    public function show(): void
    {
        $currentPage = 'calendar';
        $month = $this->requestedMonth();
        $calendarLabel = $month->format('F Y');
        $previousMonth = $month->modify('-1 month')->format('Y-m');
        $nextMonth = $month->modify('+1 month')->format('Y-m');
        $eventsByDay = [];
        foreach ($this->events->scheduledInMonth((int) $month->format('Y'), (int) $month->format('n')) as $event) {
            $label = $event['title'];
            if ($event['client'] !== '') {
                $label = $event['client'] . ' · ' . $label;
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $event['date']);
            $eventsByDay[$event['day']][] = [
                'label' => $label,
                'tone' => match ($event['status']) {
                    'confirmed' => 'confirmed',
                    'completed' => 'completed',
                    default => 'planned',
                },
                'title' => $event['title'],
                'date' => $date instanceof DateTimeImmutable ? $date->format('j F Y') : $event['date'],
                'client' => $event['client'] !== '' ? $event['client'] : '—',
                'location' => $event['location'] !== '' ? $event['location'] : '—',
                'status' => ucfirst($event['status']),
                'size' => (string) $event['attendants'],
            ];
        }
        $weeks = $this->weeks($month, $eventsByDay);

        require __DIR__ . '/../Views/calendar.php';
    }

    private function requestedMonth(): DateTimeImmutable
    {
        $value = $_GET['month'] ?? '';
        if (!is_string($value) || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) !== 1) {
            return new DateTimeImmutable('first day of this month');
        }

        $month = DateTimeImmutable::createFromFormat('!Y-m', $value);

        return $month instanceof DateTimeImmutable
            ? $month
            : new DateTimeImmutable('first day of this month');
    }

    /**
     * @param array<int, list<array{label: string, tone: string, title: string, date: string, client: string, location: string, status: string, size: string}>> $eventsByDay
     * @return list<list<array{day: int, outside: bool, today: bool, events: list<array{label: string, tone: string, title: string, date: string, client: string, location: string, status: string, size: string}>}>>
     */
    private function weeks(DateTimeImmutable $month, array $eventsByDay): array
    {
        $year = (int) $month->format('Y');
        $monthNumber = (int) $month->format('n');
        $daysInMonth = (int) $month->format('t');
        $leadingDays = ((int) $month->format('N')) - 1;
        $previousMonthDays = (int) $month->modify('-1 month')->format('t');
        $today = new DateTimeImmutable('today');
        $cells = [];

        for ($offset = $leadingDays; $offset > 0; $offset--) {
            $cells[] = [
                'day' => $previousMonthDays - $offset + 1,
                'outside' => true,
                'today' => false,
                'events' => [],
            ];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cells[] = [
                'day' => $day,
                'outside' => false,
                'today' => (int) $today->format('Y') === $year
                    && (int) $today->format('n') === $monthNumber
                    && (int) $today->format('j') === $day,
                'events' => $eventsByDay[$day] ?? [],
            ];
        }

        $nextDay = 1;
        while (count($cells) % 7 !== 0) {
            $cells[] = [
                'day' => $nextDay,
                'outside' => true,
                'today' => false,
                'events' => [],
            ];
            $nextDay++;
        }

        return array_chunk($cells, 7);
    }
}
