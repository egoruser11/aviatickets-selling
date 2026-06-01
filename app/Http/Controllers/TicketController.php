<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        return $this->view($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTicket($request);

        DB::transaction(function () use ($data): void {
            $flight = Flight::query()->lockForUpdate()->findOrFail($data['flight_id']);

            if ($this->isActiveStatus($data['status']) && $flight->seats_available < 1) {
                throw ValidationException::withMessages(['flight_id' => 'На рейсе нет свободных мест.']);
            }

            if (! in_array($data['seat_number'], $flight->seatNumbers(), true)) {
                throw ValidationException::withMessages(['seat_number' => 'Выберите место из списка мест выбранного рейса.']);
            }

            if ($this->isActiveStatus($data['status']) && Ticket::query()
                ->where('flight_id', $flight->id)
                ->where('seat_number', $data['seat_number'])
                ->whereIn('status', ['booked', 'paid'])
                ->exists()
            ) {
                throw ValidationException::withMessages(['seat_number' => 'Это место уже занято на выбранном рейсе.']);
            }

            $ticket = Ticket::query()->create($data);

            if ($this->isActiveStatus($ticket->status)) {
                $flight->decrement('seats_available');
            }
        });

        return to_route('tickets.index')->with('success', 'Билет добавлен.');
    }

    public function edit(Request $request, Ticket $ticket): View
    {
        return $this->view($request, $ticket);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $this->validateTicket($request, $ticket);

        DB::transaction(function () use ($ticket, $data): void {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $wasActive = $this->isActiveStatus($ticket->status);
            $willBeActive = $this->isActiveStatus($data['status']);
            $oldFlightId = $ticket->flight_id;

            if ($willBeActive) {
                $newFlight = Flight::query()->lockForUpdate()->findOrFail($data['flight_id']);

                $seatIsBusy = Ticket::query()
                    ->where('flight_id', $newFlight->id)
                    ->where('seat_number', $data['seat_number'])
                    ->whereIn('status', ['booked', 'paid'])
                    ->whereKeyNot($ticket->id)
                    ->exists();

                if ($seatIsBusy) {
                    throw ValidationException::withMessages(['seat_number' => 'Это место уже занято на выбранном рейсе.']);
                }

                if (! in_array($data['seat_number'], $newFlight->seatNumbers(), true)) {
                    throw ValidationException::withMessages(['seat_number' => 'Выберите место из списка мест выбранного рейса.']);
                }

                if (! $wasActive || $oldFlightId !== $newFlight->id) {
                    if ($newFlight->seats_available < 1) {
                        throw ValidationException::withMessages(['flight_id' => 'На рейсе нет свободных мест.']);
                    }

                    $newFlight->decrement('seats_available');
                }
            }

            if ($wasActive && (! $willBeActive || $oldFlightId !== (int) $data['flight_id'])) {
                Flight::query()->whereKey($oldFlightId)->increment('seats_available');
            }

            if ($ticket->status === 'paid' && $data['status'] === 'cancelled') {
                $this->refundPaidTicket($ticket, 'Возврат после отмены билета администратором');
            }

            $ticket->update($data);
        });

        return to_route('tickets.index')->with('success', 'Билет обновлен.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($ticket): void {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);

            if ($this->isActiveStatus($ticket->status)) {
                Flight::query()->whereKey($ticket->flight_id)->increment('seats_available');
            }

            if ($ticket->status === 'paid') {
                $this->refundPaidTicket($ticket, 'Возврат после удаления билета администратором');
            }

            $ticket->delete();
        });

        return to_route('tickets.index')->with('success', 'Билет удален.');
    }

    private function view(Request $request, ?Ticket $editingTicket = null): View
    {
        $sorts = [
            'passenger' => 'tickets.passenger_name',
            'flight' => 'flights.flight_number',
            'status' => 'tickets.status',
            'price' => 'tickets.price',
            'purchased' => 'tickets.purchased_at',
        ];

        $sort = array_key_exists($request->query('sort'), $sorts) ? $request->query('sort') : 'purchased';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $tickets = Ticket::query()
            ->with(['flight.airline', 'promoCode', 'user'])
            ->join('flights', 'tickets.flight_id', '=', 'flights.id')
            ->select('tickets.*')
            ->orderBy($sorts[$sort], $direction)
            ->orderBy('tickets.id')
            ->get();

        $flights = Flight::query()->with('airline')->orderBy('departure_at')->get();

        return view('tickets.index', [
            'directions' => ['asc' => 'по возрастанию', 'desc' => 'по убыванию'],
            'editingTicket' => $editingTicket,
            'flights' => $flights,
            'seatOptionsByFlight' => $flights
                ->mapWithKeys(fn (Flight $flight) => [$flight->id => $flight->seatNumbers()])
                ->all(),
            'sort' => $sort,
            'direction' => $direction,
            'statuses' => Ticket::STATUSES,
            'tickets' => $tickets,
            'users' => User::query()->where('role', User::ROLE_USER)->orderBy('email')->get(),
        ]);
    }

    private function validateTicket(Request $request, ?Ticket $ticket = null): array
    {
        $request->merge([
            'seat_number' => str($request->input('seat_number', ''))->trim()->upper()->toString(),
        ]);

        $seatNumberRules = ['required', 'string', 'max:8'];

        if ($this->isActiveStatus((string) $request->input('status'))) {
            $seatNumberRules[] = Rule::unique('tickets', 'seat_number')
                ->where(fn ($query) => $query
                    ->where('flight_id', $request->integer('flight_id'))
                    ->whereIn('status', ['booked', 'paid']))
                ->ignore($ticket);
        }

        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_USER)],
            'flight_id' => ['required', 'integer', 'exists:flights,id'],
            'seat_number' => $seatNumberRules,
            'status' => ['required', 'string', Rule::in(array_keys(Ticket::STATUSES))],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
        ]);

        $user = User::query()
            ->where('role', User::ROLE_USER)
            ->findOrFail($data['user_id']);

        $data['passenger_name'] = $user->name;
        $data['passenger_email'] = $user->email;
        $data['purchased_at'] = $ticket?->purchased_at ?? now();

        return $data;
    }

    private function isActiveStatus(string $status): bool
    {
        return in_array($status, ['booked', 'paid'], true);
    }

    private function refundPaidTicket(Ticket $ticket, string $description): void
    {
        if ($ticket->user_id === null || (float) $ticket->price <= 0) {
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
