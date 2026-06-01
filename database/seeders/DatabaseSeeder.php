<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\BalanceTransaction;
use App\Models\Flight;
use App\Models\PromoCode;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('balance_transactions')->delete();
        DB::table('personal_access_tokens')->delete();
        DB::table('tickets')->delete();
        DB::table('promo_code_usages')->delete();
        DB::table('promo_codes')->delete();
        DB::table('flights')->delete();
        DB::table('airlines')->delete();
        DB::table('users')->delete();

        $admin = User::query()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'balance' => 0,
        ]);

        $ivan = User::query()->create([
            'name' => 'Иван Петров',
            'email' => 'ivan.petrov@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'balance' => 25000,
        ]);

        $anna = User::query()->create([
            'name' => 'Анна Смирнова',
            'email' => 'anna.smirnova@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'balance' => 8000,
        ]);

        $blocked = User::query()->create([
            'name' => 'Олег Волков',
            'email' => 'oleg.volkov@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'balance' => 1500,
            'blocked_at' => now()->subDay(),
        ]);

        PromoCode::query()->create([
            'code' => 'AVIA10',
            'name' => 'Скидка 10% на покупку',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'is_active' => true,
            'max_uses' => 100,
        ]);

        PromoCode::query()->create([
            'code' => 'STUDENT500',
            'name' => 'Минус 500 рублей',
            'type' => PromoCode::TYPE_FIXED,
            'value' => 500,
            'is_active' => true,
            'max_uses' => 100,
        ]);

        PromoCode::query()->create([
            'code' => 'FAMILY15',
            'name' => 'Семейная скидка 15%',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 15,
            'is_active' => true,
            'max_uses' => 50,
        ]);

        $aeroflot = Airline::create([
            'name' => 'Аэрофлот',
            'code' => 'SU',
            'country' => 'Россия',
            'phone' => '+7 495 223-55-55',
        ]);

        $s7 = Airline::create([
            'name' => 'S7 Airlines',
            'code' => 'S7',
            'country' => 'Россия',
            'phone' => '+7 800 700-07-07',
        ]);

        $emirates = Airline::create([
            'name' => 'Emirates',
            'code' => 'EK',
            'country' => 'ОАЭ',
            'phone' => '+971 600 555555',
        ]);

        $flightOne = Flight::create([
            'airline_id' => $aeroflot->id,
            'flight_number' => 'SU-100',
            'origin' => 'Москва',
            'destination' => 'Санкт-Петербург',
            'departure_at' => Carbon::parse('2026-06-01 08:30'),
            'arrival_at' => Carbon::parse('2026-06-01 10:05'),
            'seats_total' => 180,
            'seats_available' => 179,
            'base_price' => 7500,
        ]);

        $flightTwo = Flight::create([
            'airline_id' => $s7->id,
            'flight_number' => 'S7-204',
            'origin' => 'Москва',
            'destination' => 'Новосибирск',
            'departure_at' => Carbon::parse('2026-06-02 12:10'),
            'arrival_at' => Carbon::parse('2026-06-02 16:25'),
            'seats_total' => 168,
            'seats_available' => 167,
            'base_price' => 12800,
        ]);

        $flightThree = Flight::create([
            'airline_id' => $emirates->id,
            'flight_number' => 'EK-132',
            'origin' => 'Москва',
            'destination' => 'Дубай',
            'departure_at' => Carbon::parse('2026-06-03 23:55'),
            'arrival_at' => Carbon::parse('2026-06-04 06:25'),
            'seats_total' => 302,
            'seats_available' => 301,
            'base_price' => 45200,
        ]);

        $ticketOne = Ticket::create([
            'user_id' => $ivan->id,
            'flight_id' => $flightOne->id,
            'passenger_name' => 'Иван Петров',
            'passenger_email' => 'ivan.petrov@example.com',
            'seat_number' => '12A',
            'status' => 'paid',
            'price' => 7900,
            'discount_amount' => 0,
            'purchased_at' => Carbon::parse('2026-05-20 14:15'),
        ]);

        Ticket::create([
            'user_id' => $anna->id,
            'flight_id' => $flightTwo->id,
            'passenger_name' => 'Анна Смирнова',
            'passenger_email' => 'anna.smirnova@example.com',
            'seat_number' => '8C',
            'status' => 'booked',
            'price' => 13100,
            'discount_amount' => 0,
            'purchased_at' => Carbon::parse('2026-05-21 09:40'),
        ]);

        $ticketThree = Ticket::create([
            'user_id' => $blocked->id,
            'flight_id' => $flightThree->id,
            'passenger_name' => 'Олег Волков',
            'passenger_email' => 'oleg.volkov@example.com',
            'seat_number' => '21F',
            'status' => 'paid',
            'price' => 46900,
            'discount_amount' => 0,
            'purchased_at' => Carbon::parse('2026-05-21 18:05'),
        ]);

        $ivan->balanceTransactions()->createMany([
            [
                'type' => BalanceTransaction::TYPE_TOP_UP,
                'amount' => 32900,
                'description' => 'Стартовое пополнение демонстрационного аккаунта',
            ],
            [
                'ticket_id' => $ticketOne->id,
                'type' => BalanceTransaction::TYPE_PURCHASE,
                'amount' => -7900,
                'description' => 'Покупка билета на рейс SU-100',
            ],
        ]);

        $anna->balanceTransactions()->create([
            'type' => BalanceTransaction::TYPE_TOP_UP,
            'amount' => 8000,
            'description' => 'Стартовое пополнение демонстрационного аккаунта',
        ]);

        $blocked->balanceTransactions()->createMany([
            [
                'type' => BalanceTransaction::TYPE_TOP_UP,
                'amount' => 48400,
                'description' => 'Стартовое пополнение демонстрационного аккаунта',
            ],
            [
                'ticket_id' => $ticketThree->id,
                'type' => BalanceTransaction::TYPE_PURCHASE,
                'amount' => -46900,
                'description' => 'Покупка билета на рейс EK-132',
            ],
        ]);
    }
}
