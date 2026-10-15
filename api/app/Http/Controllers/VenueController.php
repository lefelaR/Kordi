<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesResources;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    use PaginatesResources;

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        if (strlen($search) > 120) {
            $search = substr($search, 0, 120);
        }

        $query = Venue::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('location', 'like', $like);
            });
        }

        return $this->paginated($query->paginate(10), [
            'search' => $search,
        ]);
    }
}
