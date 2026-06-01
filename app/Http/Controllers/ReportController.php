<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Flight;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('flights')
            ->join('airlines', 'airlines.id', '=', 'flights.airline_id')
            ->leftJoin('tickets', 'tickets.flight_id', '=', 'flights.id')
            ->select([
                'flights.id',
                'flights.flight_number',
                'flights.origin',
                'flights.destination',
                'flights.departure_at',
                'flights.base_price',
                'airlines.name as airline_name',
                DB::raw('COUNT(tickets.id) as tickets_count'),
                DB::raw("COALESCE(SUM(CASE WHEN tickets.status = 'paid' THEN tickets.price ELSE 0 END), 0) as paid_revenue"),
            ])
            ->when($request->filled('airline_id'), fn ($query) => $query->where('airlines.id', $request->integer('airline_id')))
            ->when($request->filled('origin'), fn ($query) => $query->where('flights.origin', $request->query('origin')))
            ->when($request->filled('destination'), fn ($query) => $query->where('flights.destination', $request->query('destination')))
            ->when($request->filled('status'), fn ($query) => $query->where('tickets.status', $request->query('status')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('flights.departure_at', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('flights.departure_at', '<=', $request->query('to_date')))
            ->groupBy(
                'flights.id',
                'flights.flight_number',
                'flights.origin',
                'flights.destination',
                'flights.departure_at',
                'flights.base_price',
                'airlines.name',
            )
            ->orderBy('flights.departure_at');

        $rows = $query->get();

        return view('reports.index', [
            'airlines' => Airline::query()->orderBy('name')->get(),
            'destinations' => Flight::query()->select('destination')->distinct()->orderBy('destination')->pluck('destination'),
            'filters' => $request->only(['airline_id', 'origin', 'destination', 'status', 'from_date', 'to_date']),
            'origins' => Flight::query()->select('origin')->distinct()->orderBy('origin')->pluck('origin'),
            'rows' => $rows,
            'statuses' => Ticket::STATUSES,
            'totalRevenue' => $rows->sum('paid_revenue'),
            'totalTickets' => $rows->sum('tickets_count'),
        ]);
    }
}
