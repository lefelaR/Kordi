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
        return response()->json([
            'event_count' => Event::query()->count(),
            'vendor_count' => Vendor::query()->count(),
            'venue_count' => Venue::query()->count(),
            'enquiries' => Enquiry::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
