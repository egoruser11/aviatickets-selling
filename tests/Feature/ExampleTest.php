<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use App\Models\PromoCode;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_coursework_pages_are_available(): void
    {
        $this->seed();

        foreach (['/', '/catalog', '/login', '/register'] as $uri) {
            $this->get($uri)->assertOk();
        }

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        foreach (['/admin', '/admin/help', '/admin/users', '/admin/promo-codes', '/airlines', '/flights', '/reports', '/tickets'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_user_can_buy_multiple_tickets_with_promo_code(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'ivan.petrov@example.com')->firstOrFail();
        $flight = Flight::query()->where('flight_number', 'SU-100')->firstOrFail();
        $oldBalance = (float) $user->balance;
        $oldSeats = $flight->seats_available;

        $this->actingAs($user)
            ->post(route('catalog.buy', $flight), [
                'passenger_name' => 'Иван Петров',
                'passenger_email' => 'ivan.petrov@example.com',
                'seat_numbers' => ['15A', '15B'],
                'promo_code' => 'AVIA10',
            ])
            ->assertRedirect(route('account.tickets'));

        foreach (['15A', '15B'] as $seatNumber) {
            $this->assertDatabaseHas('tickets', [
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'seat_number' => $seatNumber,
                'status' => 'paid',
            ]);
        }

        $this->assertSame($oldBalance - 13500.0, (float) $user->fresh()->balance);
        $this->assertSame($oldSeats - 2, $flight->fresh()->seats_available);
        $this->assertDatabaseHas('promo_code_usages', [
            'user_id' => $user->id,
            'promo_code_id' => PromoCode::query()->where('code', 'AVIA10')->value('id'),
        ]);
    }

    public function test_promo_code_can_be_used_once_per_account_and_is_hidden_after_use(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'ivan.petrov@example.com')->firstOrFail();
        $flight = Flight::query()->where('flight_number', 'SU-100')->firstOrFail();

        $this->actingAs($user)
            ->post(route('catalog.buy', $flight), [
                'passenger_name' => 'Иван Петров',
                'passenger_email' => 'ivan.petrov@example.com',
                'seat_numbers' => ['15A'],
                'promo_code' => 'AVIA10',
            ])
            ->assertRedirect(route('account.tickets'));

        $this->actingAs($user)
            ->get(route('catalog.show', $flight))
            ->assertOk()
            ->assertDontSee('AVIA10')
            ->assertSee('FAMILY15');

        $this->actingAs($user)
            ->from(route('catalog.show', $flight))
            ->post(route('catalog.buy', $flight), [
                'passenger_name' => 'Иван Петров',
                'passenger_email' => 'ivan.petrov@example.com',
                'seat_numbers' => ['15B'],
                'promo_code' => 'AVIA10',
            ])
            ->assertRedirect(route('catalog.show', $flight))
            ->assertSessionHasErrors('promo_code');
    }

    public function test_blocked_user_cannot_buy_ticket(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'oleg.volkov@example.com')->firstOrFail();
        $flight = Flight::query()->where('flight_number', 'SU-100')->firstOrFail();

        $this->actingAs($user)
            ->post(route('catalog.buy', $flight), [
                'passenger_name' => 'Олег Волков',
                'passenger_email' => 'oleg.volkov@example.com',
                'seat_numbers' => ['16A'],
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('tickets', [
            'user_id' => $user->id,
            'seat_number' => '16A',
        ]);
    }

    public function test_validation_errors_are_rendered_in_blades(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('airlines.index'))
            ->followingRedirects()
            ->post(route('airlines.store'), [
                'name' => '',
                'code' => '',
                'country' => '',
            ])
            ->assertOk()
            ->assertSee('Проверьте поля формы.')
            ->assertSee('Поле название обязательно для заполнения.');
    }

    public function test_admin_balance_errors_are_shown_on_user_row(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $user = User::query()->where('email', 'anna.smirnova@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->followingRedirects()
            ->patch(route('admin.users.balance', $user), [
                'amount' => -9000,
                'description' => 'Проверка лимита списания',
            ])
            ->assertOk()
            ->assertSee('Нельзя списать больше текущего баланса.');
    }

    public function test_admin_deleting_flight_refunds_paid_tickets(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $user = User::query()->where('email', 'ivan.petrov@example.com')->firstOrFail();
        $flight = Flight::query()->where('flight_number', 'SU-100')->firstOrFail();
        $oldBalance = (float) $user->balance;

        $this->actingAs($admin)
            ->delete(route('flights.destroy', $flight))
            ->assertRedirect(route('flights.index'));

        $this->assertDatabaseMissing('flights', ['id' => $flight->id]);
        $this->assertSame($oldBalance + 7900.0, (float) $user->fresh()->balance);
    }

    public function test_admin_promo_code_validation_rejects_unreasonable_values(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.promo-codes.index'))
            ->post(route('admin.promo-codes.store'), [
                'code' => 'TOO-MUCH',
                'type' => PromoCode::TYPE_PERCENT,
                'value' => 5000,
                'max_uses' => 1000000000,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.index'))
            ->assertSessionHasErrors(['value', 'max_uses']);

        $this->assertDatabaseMissing('promo_codes', ['code' => 'TOO-MUCH']);
    }

    public function test_admin_promo_code_validation_normalizes_code_before_unique_check(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.promo-codes.index'))
            ->post(route('admin.promo-codes.store'), [
                'code' => ' avia10 ',
                'type' => PromoCode::TYPE_PERCENT,
                'value' => 10,
                'max_uses' => 100,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.index'))
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_create_reasonable_fixed_promo_code(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.promo-codes.store'), [
                'code' => ' spring-1500 ',
                'name' => ' Весенняя скидка ',
                'type' => PromoCode::TYPE_FIXED,
                'value' => 1500,
                'max_uses' => 250,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.index'));

        $this->assertDatabaseHas('promo_codes', [
            'code' => 'SPRING-1500',
            'name' => 'Весенняя скидка',
            'type' => PromoCode::TYPE_FIXED,
            'value' => 1500,
            'max_uses' => 250,
        ]);
    }

    public function test_admin_cannot_reduce_promo_limit_below_completed_uses(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $promoCode = PromoCode::query()->where('code', 'AVIA10')->firstOrFail();
        $promoCode->update(['used_count' => 3]);

        $this->actingAs($admin)
            ->from(route('admin.promo-codes.edit', $promoCode))
            ->put(route('admin.promo-codes.update', $promoCode), [
                'code' => $promoCode->code,
                'name' => $promoCode->name,
                'type' => $promoCode->type,
                'value' => $promoCode->value,
                'max_uses' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.promo-codes.edit', $promoCode))
            ->assertSessionHasErrors('max_uses');

        $this->assertSame(100, $promoCode->fresh()->max_uses);
    }

    public function test_admin_can_store_cancelled_ticket_for_seat_that_is_currently_busy(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $user = User::query()->where('email', 'anna.smirnova@example.com')->firstOrFail();
        $flight = Flight::query()->where('flight_number', 'SU-100')->firstOrFail();
        $beforeCreation = now()->startOfSecond();

        $this->actingAs($admin)
            ->post(route('tickets.store'), [
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'passenger_name' => 'Подмененное имя',
                'passenger_email' => 'spoof@example.com',
                'seat_number' => '12A',
                'status' => 'cancelled',
                'price' => 0,
                'purchased_at' => '2026-05-01 10:00:00',
            ])
            ->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'flight_id' => $flight->id,
            'user_id' => $user->id,
            'passenger_name' => $user->name,
            'passenger_email' => $user->email,
            'seat_number' => '12A',
            'status' => 'cancelled',
        ]);

        $ticket = Ticket::query()
            ->where('flight_id', $flight->id)
            ->where('user_id', $user->id)
            ->where('status', 'cancelled')
            ->latest('id')
            ->firstOrFail();

        $this->assertTrue($ticket->purchased_at->greaterThanOrEqualTo($beforeCreation));
        $this->assertNotSame('2026-05-01 10:00:00', $ticket->purchased_at->format('Y-m-d H:i:s'));
    }

    public function test_admin_identifier_validation_normalizes_airline_code_and_generates_flight_number(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $airline = Airline::query()->where('code', 'SU')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('airlines.index'))
            ->post(route('airlines.store'), [
                'name' => 'Дубликат',
                'code' => ' su ',
                'country' => 'Россия',
            ])
            ->assertRedirect(route('airlines.index'))
            ->assertSessionHasErrors('code');

        $this->actingAs($admin)
            ->post(route('flights.store'), [
                'airline_id' => $airline->id,
                'flight_number' => 'MANUAL-999',
                'origin' => 'Москва',
                'destination' => 'Казань',
                'departure_at' => '2026-06-10 10:00:00',
                'arrival_at' => '2026-06-10 11:30:00',
                'seats_total' => 120,
                'seats_available' => 120,
                'base_price' => 5500,
            ])
            ->assertRedirect(route('flights.index'));

        $flight = Flight::query()
            ->where('origin', 'Москва')
            ->where('destination', 'Казань')
            ->firstOrFail();

        $this->assertSame("SU-{$flight->id}", $flight->flight_number);
    }

    public function test_registration_normalizes_name_and_email(): void
    {
        $this->post(route('register.store'), [
            'name' => ' Новый пользователь ',
            'email' => ' New.User@Example.COM ',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('account.balance'));

        $this->assertDatabaseHas('users', [
            'name' => 'Новый пользователь',
            'email' => 'new.user@example.com',
        ]);
    }
}
