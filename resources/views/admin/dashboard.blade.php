@extends('layouts.app')

@section('title', 'Панель администратора')

@section('content')
    <div class="page-title">
        <div>
            <h1>Панель администратора</h1>
            <p class="muted">Управление рейсами, билетами, пользователями и отчетами курсового проекта.</p>
        </div>
        <a class="button primary" href="{{ route('admin.help') }}">Инструкция</a>
    </div>

    <section class="menu-grid">
        <a class="menu-link" href="{{ route('airlines.index') }}">
            <strong>Авиакомпании</strong>
            Справочник перевозчиков: {{ $airlinesCount }} записей.
        </a>
        <a class="menu-link" href="{{ route('flights.index') }}">
            <strong>Рейсы</strong>
            Расписание, цены и свободные места: {{ $flightsCount }} рейсов.
        </a>
        <a class="menu-link" href="{{ route('tickets.index') }}">
            <strong>Билеты</strong>
            Продажи и бронирования: {{ $ticketsCount }} оплаченных.
        </a>
        <a class="menu-link" href="{{ route('admin.users.index') }}">
            <strong>Пользователи</strong>
            Покупатели: {{ $usersCount }}, заблокировано: {{ $blockedUsersCount }}.
        </a>
        <a class="menu-link" href="{{ route('admin.promo-codes.index') }}">
            <strong>Промокоды</strong>
            Активные скидки для покупателей: {{ $promoCodesCount }}.
        </a>
    </section>

    <section class="section">
        <h2>Последние билеты</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Покупатель</th>
                        <th>Рейс</th>
                        <th>Пассажир</th>
                        <th>Статус</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $ticket->user?->name ?? 'Админская запись' }}</td>
                            <td>{{ $ticket->flight->flight_number }}</td>
                            <td>{{ $ticket->passenger_name }}</td>
                            <td>{{ $ticket->statusLabel() }}</td>
                            <td>{{ number_format((float) $ticket->price, 2, ',', ' ') }} руб.</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Билетов пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
