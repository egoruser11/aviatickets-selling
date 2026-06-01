<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'airlinesCount' => Airline::query()->count(),
            'flightsCount' => Flight::query()->count(),
            'ticketsCount' => Ticket::query()->count(),
            'usersCount' => User::query()->where('role', User::ROLE_USER)->count(),
        ]);
    }
}
