<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Event;
use App\Models\Vendor;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $year = filter_var($request->query('year'), FILTER_VALIDATE_INT);
        $month = filter_var($request->query('month'), FILTER_VALIDATE_INT);
        $year = is_int($year) && $year >= 2000 && $year <= 2100 ? $year : (int) date('Y');
        $month = is_int($month) && $month >= 1 && $month <= 12 ? $month : (int) date('n');

        $revenueEvents = Event::query()
            ->whereNotNull('event_date')
            ->whereNotNull('revenue')
            ->whereYear('event_date', $year)
            ->where('revenue', '>', 0)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get(['id', 'title', 'company', 'event_date', 'revenue']);

        $chartEvents = [];
        $totalRevenue = 0.0;

        foreach ($revenueEvents as $event) {
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

        $calendarEvents = Event::query()
            ->whereNotNull('event_date')
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get(['id', 'title', 'company', 'event_date', 'status']);

        $calendarItems = [];
        foreach ($calendarEvents as $event) {
            $calendarItems[] = [
                'id' => (int) $event->id,
                'title' => (string) $event->title,
                'client' => trim((string) ($event->company ?? '')) !== ''
                    ? (string) $event->company
                    : '',
                'day' => (int) $event->event_date->format('j'),
                'date' => $event->event_date->format('Y-m-d'),
                'status' => (string) $event->status,
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
            'calendar' => [
                'year' => $year,
                'month' => $month,
                'events' => $calendarItems,
            ],
        ]);
    }
}
