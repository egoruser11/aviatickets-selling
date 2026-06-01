@extends('layouts.app')

@section('title', 'Мои билеты')

@section('content')
    <div class="page-title">
        <div>
            <h1>Мои билеты</h1>
            <p class="muted">История покупок и отмен в вашем аккаунте.</p>
        </div>
        <a class="button primary" href="{{ route('catalog.index') }}">Купить билет</a>
    </div>

    <section class="section">
        <h2>Билеты аккаунта</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Рейс</th>
                        <th>Маршрут</th>
                        <th>Пассажир</th>
                        <th>Место</th>
                        <th>Статус</th>
                        <th>Цена</th>
                        <th>Дата</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->flight->flight_number }}</td>
                            <td>{{ $ticket->flight->origin }} - {{ $ticket->flight->destination }}</td>
                            <td>{{ $ticket->passenger_name }}<br><span class="muted">{{ $ticket->passenger_email }}</span></td>
                            <td>{{ $ticket->seat_number }}</td>
                            <td>
                                <span class="badge {{ $ticket->status === 'cancelled' ? 'danger' : 'success' }}">{{ $ticket->statusLabel() }}</span>
                            </td>
                            <td>
                                {{ number_format((float) $ticket->price, 2, ',', ' ') }} руб.
                                @if ((float) $ticket->discount_amount > 0)
                                    <br><span class="muted">скидка {{ number_format((float) $ticket->discount_amount, 2, ',', ' ') }} руб.</span>
                                @endif
                            </td>
                            <td>{{ $ticket->purchased_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if ($ticket->status !== 'cancelled')
                                    <form method="post" action="{{ route('account.tickets.cancel', $ticket) }}" onsubmit="return confirm('Отменить билет и вернуть деньги на баланс?')">
                                        @csrf
                                        @method('patch')
                                        <button class="button small danger" type="submit">Отменить</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">У вас пока нет билетов.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
