<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesResources;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    use PaginatesResources;

    public function index(Request $request): JsonResponse
    {
        $paginator = Vendor::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return $this->paginated($paginator);
    }
}
