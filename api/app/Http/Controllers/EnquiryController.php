<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;

class EnquiryController extends Controller
{
    public function index(): JsonResponse
    {
        $enquiries = Enquiry::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $enquiries,
        ]);
    }
}
