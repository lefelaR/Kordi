<?php

namespace App\Models;

class DashboardApi
{
    public function __construct(private ApiClient $client)
    {
    }

    /**
     * @return array{
     *     event_count: int,
     *     vendor_count: int,
     *     venue_count: int,
     *     enquiries: list<array<string, mixed>>,
     *     revenue_total: float,
     *     event_revenue_chart: array{year: int, events: list<array{id: int, title: string, client: string, month: int, revenue: float}>}
     * }
     */
    public function overview(): array
    {
        $response = $this->client->get('/overview');
        $chart = is_array($response['event_revenue_chart'] ?? null)
            ? $response['event_revenue_chart']
            : [];
        $chartEvents = [];
        foreach (($chart['events'] ?? []) as $event) {
            if (!is_array($event)) {
                continue;
            }
            $chartEvents[] = [
                'id' => (int) ($event['id'] ?? 0),
                'title' => (string) ($event['title'] ?? ''),
                'client' => (string) ($event['client'] ?? 'No client'),
                'month' => max(1, min(12, (int) ($event['month'] ?? 1))),
                'revenue' => (float) ($event['revenue'] ?? 0),
            ];
        }

        return [
            'event_count' => (int) ($response['event_count'] ?? 0),
            'vendor_count' => (int) ($response['vendor_count'] ?? 0),
            'venue_count' => (int) ($response['venue_count'] ?? 0),
            'enquiries' => $this->normalizeRows($response['enquiries'] ?? []),
            'revenue_total' => (float) ($response['revenue_total'] ?? 0),
            'event_revenue_chart' => [
                'year' => (int) ($chart['year'] ?? date('Y')),
                'events' => $chartEvents,
            ],
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array{total: int, page: int, per_page: int, last_page: int}, status_counts: array<string, int>}
     */
    public function events(int $page): array
    {
        $response = $this->client->get('/events', ['page' => $page]);
        $statusCounts = [];
        foreach (($response['status_counts'] ?? []) as $status => $total) {
            $statusCounts[(string) $status] = (int) $total;
        }

        return [
            'data' => $this->normalizeRows($response['data'] ?? []),
            'meta' => $this->normalizeMeta($response['meta'] ?? []),
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array{total: int, page: int, per_page: int, last_page: int}}
     */
    public function vendors(int $page): array
    {
        $response = $this->client->get('/vendors', ['page' => $page]);

        return [
            'data' => $this->normalizeRows($response['data'] ?? []),
            'meta' => $this->normalizeMeta($response['meta'] ?? []),
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array{total: int, page: int, per_page: int, last_page: int}}
     */
    public function companies(int $page): array
    {
        $response = $this->client->get('/companies', ['page' => $page]);

        return [
            'data' => $this->normalizeRows($response['data'] ?? []),
            'meta' => $this->normalizeMeta($response['meta'] ?? []),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function companiesAll(): array
    {
        $response = $this->client->get('/companies/all');

        return $this->normalizeRows($response['data'] ?? []);
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array{total: int, page: int, per_page: int, last_page: int}, search: string}
     */
    public function venues(int $page, string $search = ''): array
    {
        $query = ['page' => $page];
        if ($search !== '') {
            $query['q'] = $search;
        }

        $response = $this->client->get('/venues', $query);

        return [
            'data' => $this->normalizeRows($response['data'] ?? []),
            'meta' => $this->normalizeMeta($response['meta'] ?? []),
            'search' => (string) ($response['search'] ?? $search),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function enquiries(): array
    {
        $response = $this->client->get('/enquiries');

        return $this->normalizeRows($response['data'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{total: int, page: int, per_page: int, last_page: int}
     */
    private function normalizeMeta(array $meta): array
    {
        return [
            'total' => (int) ($meta['total'] ?? 0),
            'page' => max(1, (int) ($meta['page'] ?? 1)),
            'per_page' => max(1, (int) ($meta['per_page'] ?? 10)),
            'last_page' => max(1, (int) ($meta['last_page'] ?? 1)),
        ];
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $normalized[] = $this->normalizeRow($row);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        foreach (['event_date', 'created_at'] as $dateField) {
            if (!isset($row[$dateField]) || !is_string($row[$dateField])) {
                continue;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $row[$dateField], $matches) === 1) {
                $row[$dateField] = substr($row[$dateField], 0, 10) === $matches[0] && strlen($row[$dateField]) > 10
                    ? (str_contains($row[$dateField], 'T') ? substr($row[$dateField], 0, 19) : $row[$dateField])
                    : $row[$dateField];
            }
            if (str_contains($row[$dateField], 'T')) {
                $row[$dateField] = str_replace('T', ' ', substr($row[$dateField], 0, 19));
            }
        }

        if (isset($row['event_date']) && is_string($row['event_date']) && preg_match('/^\d{4}-\d{2}-\d{2}/', $row['event_date']) === 1) {
            $row['event_date'] = substr($row['event_date'], 0, 10);
        }

        return $row;
    }
}
