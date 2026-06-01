<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\Flight;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function tickets(): View
    {
        return view('account.tickets', [
            'tickets' => Auth::user()
                ->tickets()
                ->with('flight.airline')
                ->latest()
                ->get(),
        ]);
    }

    public function balance(): View
    {
        return view('account.balance', [
            'transactions' => Auth::user()
                ->balanceTransactions()
                ->with('ticket.flight')
                ->latest()
                ->get(),
        ]);
    }

    public function topUp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:100', 'max:500000'],
        ]);

        DB::transaction(function () use ($data): void {
            /** @var User $user */
            $user = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $amount = (float) $data['amount'];

            $user->increment('balance', $amount);
            $user->balanceTransactions()->create([
                'type' => BalanceTransaction::TYPE_TOP_UP,
                'amount' => $amount,
                'description' => 'Пополнение баланса пользователем',
            ]);
        });

        return back()->with('success', 'Баланс пополнен.');
    }

    public function buy(Request $request, Flight $flight): RedirectResponse
    {
        $seatNumbers = collect((array) $request->input('seat_numbers', []))
            ->map(fn ($seat) => trim((string) $seat))
            ->filter()
            ->values()
            ->all();

        $promoCode = str($request->input('promo_code', ''))->trim()->upper()->toString();

        $request->merge([
            'passenger_name' => trim((string) $request->input('passenger_name', '')),
            'passenger_email' => str($request->input('passenger_email', ''))->trim()->lower()->toString(),
            'seat_numbers' => $seatNumbers,
            'promo_code' => $promoCode !== '' ? $promoCode : null,
        ]);

        $data = $request->validate([
            'passenger_name' => ['required', 'string', 'max:255'],
            'passenger_email' => ['required', 'email', 'max:255'],
            'seat_numbers' => ['required', 'array', 'min:1', 'max:6'],
            'seat_numbers.*' => ['required', 'string', 'max:8', 'distinct'],
            'promo_code' => ['nullable', 'string', 'max:32'],
        ]);

        DB::transaction(function () use ($data, $flight): void {
            /** @var User $user */
            $user = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $flight = Flight::query()->lockForUpdate()->findOrFail($flight->id);
            $seatNumbers = array_values($data['seat_numbers']);
            $ticketsCount = count($seatNumbers);
            $basePrice = (float) $flight->base_price;

            if ($flight->seats_available < $ticketsCount) {
                throw ValidationException::withMessages(['flight' => 'На рейсе недостаточно свободных мест для выбранного количества билетов.']);
            }

            $unknownSeats = array_diff($seatNumbers, $flight->seatNumbers());

            if ($unknownSeats !== []) {
                throw ValidationException::withMessages(['seat_numbers' => 'Выберите место из списка доступных мест.']);
            }

            $busySeats = Ticket::query()
                ->where('flight_id', $flight->id)
                ->whereIn('seat_number', $seatNumbers)
                ->whereIn('status', ['booked', 'paid'])
                ->pluck('seat_number')
                ->all();

            if ($busySeats !== []) {
                throw ValidationException::withMessages(['seat_numbers' => 'Некоторые выбранные места уже заняты: '.implode(', ', $busySeats).'.']);
            }

            $promoCode = null;

            if (! empty($data['promo_code'])) {
                $promoCode = PromoCode::query()
                    ->where('code', $data['promo_code'])
                    ->lockForUpdate()
                    ->first();

                if (! $promoCode?->isUsable()) {
                    throw ValidationException::withMessages(['promo_code' => 'Промокод не найден, отключен или уже закончился.']);
                }

                $alreadyUsed = PromoCodeUsage::query()
                    ->where('user_id', $user->id)
                    ->where('promo_code_id', $promoCode->id)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyUsed) {
                    throw ValidationException::withMessages(['promo_code' => 'Этот промокод уже использован вашим аккаунтом.']);
                }
            }

            $subtotal = round($basePrice * $ticketsCount, 2);
            $discount = $promoCode?->discountFor($subtotal) ?? 0.0;
            $total = round($subtotal - $discount, 2);

            if ((float) $user->balance < $total) {
                throw ValidationException::withMessages(['balance' => 'Недостаточно средств на балансе.']);
            }

            $priceParts = $this->allocateCents($this->toCents($total), $ticketsCount);
            $discountParts = $this->allocateCents($this->toCents($discount), $ticketsCount);
            $firstTicket = null;

            foreach ($seatNumbers as $index => $seatNumber) {
                $ticket = Ticket::query()->create([
                    'user_id' => $user->id,
                    'flight_id' => $flight->id,
                    'promo_code_id' => $promoCode?->id,
                    'passenger_name' => $data['passenger_name'],
                    'passenger_email' => $data['passenger_email'],
                    'seat_number' => $seatNumber,
                    'status' => 'paid',
                    'price' => $priceParts[$index] / 100,
                    'discount_amount' => $discountParts[$index] / 100,
                    'purchased_at' => now(),
                ]);

                $firstTicket ??= $ticket;
            }

            $user->decrement('balance', $total);
            $flight->decrement('seats_available', $ticketsCount);

            if ($promoCode) {
                PromoCodeUsage::query()->create([
                    'user_id' => $user->id,
                    'promo_code_id' => $promoCode->id,
                ]);
                $promoCode->increment('used_count');
            }

            $user->balanceTransactions()->create([
                'ticket_id' => $firstTicket?->id,
                'type' => BalanceTransaction::TYPE_PURCHASE,
                'amount' => -$total,
                'description' => "Покупка {$ticketsCount} билет(ов) на рейс {$flight->flight_number}"
                    .($promoCode ? " с промокодом {$promoCode->code}" : ''),
            ]);
        });

        return redirect()->route('account.tickets')->with('success', 'Билеты куплены и сохранены в вашем аккаунте.');
    }

    public function cancel(Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($ticket): void {
            /** @var Ticket $ticket */
            $ticket = Ticket::query()->with('flight')->lockForUpdate()->findOrFail($ticket->id);

            if ($ticket->status === 'cancelled') {
                throw ValidationException::withMessages(['ticket' => 'Этот билет уже отменен.']);
            }

            /** @var User $user */
            $user = User::query()->lockForUpdate()->findOrFail($ticket->user_id);
            $price = (float) $ticket->price;

            $ticket->update(['status' => 'cancelled']);
            $ticket->flight()->increment('seats_available');
            $user->increment('balance', $price);

            $user->balanceTransactions()->create([
                'ticket_id' => $ticket->id,
                'type' => BalanceTransaction::TYPE_REFUND,
                'amount' => $price,
                'description' => "Возврат билета на рейс {$ticket->flight->flight_number}",
            ]);
        });

        return back()->with('success', 'Билет отменен, стоимость возвращена на баланс.');
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * @return array<int, int>
     */
    private function allocateCents(int $totalCents, int $parts): array
    {
        $base = intdiv($totalCents, $parts);
        $remainder = $totalCents % $parts;

        return collect(range(1, $parts))
            ->map(fn ($index) => $base + ($index <= $remainder ? 1 : 0))
            ->all();
    }
}
