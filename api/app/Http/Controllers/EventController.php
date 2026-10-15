<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesResources;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use PaginatesResources;

    public function index(Request $request): JsonResponse
    {
        $perPage = 10;
        $paginator = Event::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $statusCounts = Event::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status')
            ->map(static fn ($total) => (int) $total)
            ->all();

        $normalized = [];
        foreach ($statusCounts as $status => $total) {
            $key = $status !== '' && $status !== null ? (string) $status : 'Unspecified';
            $normalized[$key] = $total;
        }

        return $this->paginated($paginator, [
            'status_counts' => $normalized,
        ]);
    }
}
