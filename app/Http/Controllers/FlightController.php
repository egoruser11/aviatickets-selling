<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\BalanceTransaction;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FlightController extends Controller
{
    public function index(Request $request): View
    {
        return $this->view($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateFlight($request);

        $flight = DB::transaction(function () use ($data): Flight {
            $flight = Flight::query()->create([
                ...$data,
                'flight_number' => 'TMP-'.Str::upper(Str::random(12)),
            ]);

            $flight->update([
                'flight_number' => $this->automaticFlightNumber($flight),
            ]);

            return $flight;
        });

        return to_route('flights.index')->with('success', "Рейс {$flight->flight_number} добавлен.");
    }

    public function edit(Request $request, Flight $flight): View
    {
        return $this->view($request, $flight);
    }

    public function update(Request $request, Flight $flight): RedirectResponse
    {
        $data = $this->validateFlight($request);

        DB::transaction(function () use ($data, $flight): void {
            $flight->update($data);
            $flight->update([
                'flight_number' => $this->automaticFlightNumber($flight),
            ]);
        });

        return to_route('flights.index')->with('success', "Рейс {$flight->flight_number} обновлен.");
    }

    public function destroy(Flight $flight): RedirectResponse
    {
        DB::transaction(function () use ($flight): void {
            $flight = Flight::query()
                ->with('tickets')
                ->lockForUpdate()
                ->findOrFail($flight->id);

            $tickets = $flight->tickets()
                ->where('status', 'paid')
                ->whereNotNull('user_id')
                ->get();

            foreach ($tickets as $ticket) {
                /** @var Ticket $ticket */
                $this->refundPaidTicket($ticket, "Возврат из-за удаления рейса {$flight->flight_number}");
            }

            $flight->delete();
        });

        return to_route('flights.index')->with('success', 'Рейс удален, оплаченные билеты возвращены на баланс покупателей.');
    }

    private function view(Request $request, ?Flight $editingFlight = null): View
    {
        $search = trim((string) $request->query('search', ''));

        $flights = Flight::query()
            ->with(['airline', 'tickets'])
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

        return view('flights.index', [
            'airlines' => Airline::query()->orderBy('name')->get(),
            'editingFlight' => $editingFlight,
            'flights' => $flights,
            'nextFlightId' => (int) Flight::query()->max('id') + 1,
            'search' => $search,
        ]);
    }

    private function validateFlight(Request $request): array
    {
        $request->merge([
            'origin' => trim((string) $request->input('origin', '')),
            'destination' => trim((string) $request->input('destination', '')),
        ]);

        return $request->validate([
            'airline_id' => ['required', 'integer', 'exists:airlines,id'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255', 'different:origin'],
            'departure_at' => ['required', 'date'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'seats_total' => ['required', 'integer', 'min:1', 'max:999'],
            'seats_available' => ['required', 'integer', 'min:0', 'lte:seats_total'],
            'base_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
        ]);
    }

    private function automaticFlightNumber(Flight $flight): string
    {
        $flight->load('airline');
        $flightNumber = "{$flight->airline->code}-{$flight->id}";

        if (mb_strlen($flightNumber) > 16) {
            throw ValidationException::withMessages([
                'airline_id' => 'Не удалось сформировать номер рейса: код авиакомпании слишком длинный.',
            ]);
        }

        return $flightNumber;
    }

    private function refundPaidTicket(Ticket $ticket, string $description): void
    {
        if ($ticket->status !== 'paid' || $ticket->user_id === null || (float) $ticket->price <= 0) {
            return;
        }

        $user = User::query()->lockForUpdate()->find($ticket->user_id);

        if (! $user) {
            return;
        }

        $amount = (float) $ticket->price;
        $user->increment('balance', $amount);
        $user->balanceTransactions()->create([
            'ticket_id' => $ticket->id,
            'type' => BalanceTransaction::TYPE_REFUND,
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
