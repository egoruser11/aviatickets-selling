<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use Illuminate\Http\JsonResponse;

class FlightController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Flight::query()
                ->with('airline')
                ->where('departure_at', '>=', now())
                ->orderBy('departure_at')
                ->get(),
        ]);
    }
}
