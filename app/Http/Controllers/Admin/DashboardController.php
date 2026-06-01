<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\PromoCode;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'airlinesCount' => Airline::query()->count(),
            'flightsCount' => Flight::query()->count(),
            'promoCodesCount' => PromoCode::query()->where('is_active', true)->count(),
            'ticketsCount' => Ticket::query()->where('status', 'paid')->count(),
            'usersCount' => User::query()->where('role', User::ROLE_USER)->count(),
            'blockedUsersCount' => User::query()->whereNotNull('blocked_at')->count(),
            'latestTickets' => Ticket::query()->with(['user', 'flight.airline'])->latest()->limit(6)->get(),
        ]);
    }
}
