@extends('layouts.app')

@section('title', 'Форма 4 - запросы')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 4 · Запросы</h1>
            <p class="muted">Результаты по нескольким связанным таблицам.</p>
        </div>
    </div>

    <section class="section">
        <h2>Параметры запроса</h2>
        <form method="get" action="{{ route('reports.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="airline_id">Авиакомпания</label>
                    <select id="airline_id" name="airline_id">
                        <option value="">Все</option>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline->id }}" @selected((int) ($filters['airline_id'] ?? 0) === $airline->id)>
                                {{ $airline->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="origin">Откуда</label>
                    <select id="origin" name="origin">
                        <option value="">Все города</option>
                        @foreach ($origins as $origin)
                            <option value="{{ $origin }}" @selected(($filters['origin'] ?? '') === $origin)>{{ $origin }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="destination">Куда</label>
                    <select id="destination" name="destination">
                        <option value="">Все города</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination }}" @selected(($filters['destination'] ?? '') === $destination)>{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="status">Статус билета</label>
                    <select id="status" name="status">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="from_date">Дата с</label>
                    <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="to_date">Дата по</label>
                    <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Выполнить</button>
                <a class="button" href="{{ route('reports.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Результаты</h2>
        <div class="summary">
            <span>Билетов: {{ $totalTickets }}</span>
            <span>Оплачено: {{ number_format((float) $totalRevenue, 2, ',', ' ') }} руб.</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Рейс</th>
                    <th>Авиакомпания</th>
                    <th>Маршрут</th>
                    <th>Вылет</th>
                    <th>Базовая цена</th>
                    <th>Билетов</th>
                    <th>Выручка</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->flight_number }}</td>
                        <td>{{ $row->airline_name }}</td>
                        <td>{{ $row->origin }} - {{ $row->destination }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($row->departure_at)->format('d.m.Y H:i') }}</td>
                        <td>{{ number_format((float) $row->base_price, 2, ',', ' ') }} руб.</td>
                        <td>{{ $row->tickets_count }}</td>
                        <td>{{ number_format((float) $row->paid_revenue, 2, ',', ' ') }} руб.</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Нет данных по заданным параметрам.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
