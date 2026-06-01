@extends('layouts.app')

@section('title', 'Каталог рейсов')

@section('content')
    <div class="page-title">
        <div>
            <h1>Каталог рейсов</h1>
            <p class="muted">Доступные рейсы для покупки билетов пользователями.</p>
        </div>
        @auth
            @if (! auth()->user()->isAdmin())
                <a class="button primary" href="{{ route('account.balance') }}">Баланс: {{ number_format((float) auth()->user()->balance, 2, ',', ' ') }} руб.</a>
            @endif
        @endauth
    </div>

    <section class="section">
        <h2>Поиск</h2>
        <form method="get" action="{{ route('catalog.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Номер, город или авиакомпания</label>
                    <input id="search" name="search" value="{{ $search }}">
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('catalog.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Рейсы</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Рейс</th>
                        <th>Авиакомпания</th>
                        <th>Маршрут</th>
                        <th>Вылет</th>
                        <th>Прилет</th>
                        <th>Места</th>
                        <th>Цена</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($flights as $flight)
                        <tr>
                            <td>{{ $flight->flight_number }}</td>
                            <td>{{ $flight->airline->name }}</td>
                            <td>{{ $flight->origin }} - {{ $flight->destination }}</td>
                            <td>{{ $flight->departure_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $flight->arrival_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $flight->seats_available }} из {{ $flight->seats_total }}</td>
                            <td>{{ number_format((float) $flight->base_price, 2, ',', ' ') }} руб.</td>
                            <td><a class="button small" href="{{ route('catalog.show', $flight) }}">Открыть</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Подходящие рейсы не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
