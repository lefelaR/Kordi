<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Event;
use App\Models\Vendor;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;

class OverviewController extends Controller
{
    public function show(): JsonResponse
    {
        $year = (int) date('Y');

        $events = Event::query()
            ->whereNotNull('event_date')
            ->whereNotNull('revenue')
            ->whereYear('event_date', $year)
            ->where('revenue', '>', 0)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get(['id', 'title', 'company', 'event_date', 'revenue']);

        $chartEvents = [];
        $totalRevenue = 0.0;

        foreach ($events as $event) {
            $revenue = (float) $event->revenue;
            $totalRevenue += $revenue;
            $chartEvents[] = [
                'id' => (int) $event->id,
                'title' => (string) $event->title,
                'client' => trim((string) ($event->company ?? '')) !== ''
                    ? (string) $event->company
                    : 'No client',
                'month' => (int) $event->event_date->format('n'),
                'revenue' => round($revenue, 2),
            ];
        }

        return response()->json([
            'event_count' => Event::query()->count(),
            'vendor_count' => Vendor::query()->count(),
            'venue_count' => Venue::query()->count(),
            'enquiries' => Enquiry::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(),
            'revenue_total' => round($totalRevenue, 2),
            'event_revenue_chart' => [
                'year' => $year,
                'events' => $chartEvents,
            ],
        ]);
    }
}
