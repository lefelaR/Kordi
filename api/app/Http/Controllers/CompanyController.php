<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesResources;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use PaginatesResources;

    public function index(Request $request): JsonResponse
    {
        $paginator = Company::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return $this->paginated($paginator);
    }

    public function all(): JsonResponse
    {
        $companies = Company::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'contact', 'email', 'phone', 'created_at']);

        return response()->json([
            'data' => $companies,
        ]);
    }
}
