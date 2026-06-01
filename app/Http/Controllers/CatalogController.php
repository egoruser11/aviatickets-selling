<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $flights = Flight::query()
            ->with('airline')
            ->where('departure_at', '>=', now())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('flight_number', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%")
                        ->orWhereHas('airline', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('departure_at')
            ->get();

        return view('catalog.index', [
            'flights' => $flights,
            'search' => $search,
        ]);
    }

    public function show(Flight $flight): View
    {
        $flight->load(['airline', 'tickets']);
        $busySeats = $flight->tickets()
            ->whereIn('status', ['booked', 'paid'])
            ->orderBy('seat_number')
            ->pluck('seat_number');
        $usedPromoCodeIds = Auth::check()
            ? Auth::user()->promoCodeUsages()->pluck('promo_code_id')->all()
            : [];

        return view('catalog.show', [
            'availableSeats' => collect($flight->seatNumbers())->diff($busySeats)->values(),
            'busySeats' => $busySeats,
            'flight' => $flight,
            'promoCodes' => PromoCode::query()
                ->where('is_active', true)
                ->when($usedPromoCodeIds !== [], fn ($query) => $query->whereNotIn('id', $usedPromoCodeIds))
                ->orderBy('code')
                ->get()
                ->filter->isUsable()
                ->values(),
        ]);
    }
}
