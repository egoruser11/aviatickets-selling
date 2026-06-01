@extends('layouts.app')

@section('title', 'Форма 3 - рейсы')

@section('content')
    @php
        $currentAirlineId = (int) old('airline_id', $editingFlight->airline_id ?? optional($airlines->first())->id);
        $currentAirline = $airlines->firstWhere('id', $currentAirlineId);
        $flightNumberPreview = ($currentAirline?->code ?? 'КОД').'-'.($editingFlight->id ?? $nextFlightId);
    @endphp

    <div class="page-title">
        <div>
            <h1>Форма 3 · Рейсы</h1>
            <p class="muted">Таблица №2 с редактированием записей.</p>
        </div>
    </div>

    <section class="section">
        <h2>{{ $editingFlight ? 'Изменение рейса' : 'Добавление рейса' }}</h2>
        <form method="post" action="{{ $editingFlight ? route('flights.update', $editingFlight) : route('flights.store') }}">
            @csrf
            @if ($editingFlight)
                @method('put')
            @endif
            <div class="grid">
                <div class="field wide">
                    <label for="airline_id">Авиакомпания</label>
                    <select id="airline_id" name="airline_id" class="@error('airline_id') is-invalid @enderror" required>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline->id }}" data-code="{{ $airline->code }}" @selected($currentAirlineId === $airline->id)>
                                {{ $airline->name }} ({{ $airline->code }})
                            </option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'airline_id'])
                </div>
                <div class="field">
                    <label for="flight_number_preview">Номер рейса</label>
                    <input id="flight_number_preview" value="{{ $flightNumberPreview }}" readonly>
                </div>
                <div class="field">
                    <label for="origin">Откуда</label>
                    <input id="origin" name="origin" value="{{ old('origin', $editingFlight->origin ?? '') }}" class="@error('origin') is-invalid @enderror" maxlength="255" required>
                    @include('partials.field-error', ['field' => 'origin'])
                </div>
                <div class="field">
                    <label for="destination">Куда</label>
                    <input id="destination" name="destination" value="{{ old('destination', $editingFlight->destination ?? '') }}" class="@error('destination') is-invalid @enderror" maxlength="255" required>
                    @include('partials.field-error', ['field' => 'destination'])
                </div>
                <div class="field">
                    <label for="departure_at">Вылет</label>
                    <input id="departure_at" type="datetime-local" name="departure_at" value="{{ old('departure_at', optional($editingFlight?->departure_at)->format('Y-m-d\TH:i')) }}" class="@error('departure_at') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'departure_at'])
                </div>
                <div class="field">
                    <label for="arrival_at">Прилет</label>
                    <input id="arrival_at" type="datetime-local" name="arrival_at" value="{{ old('arrival_at', optional($editingFlight?->arrival_at)->format('Y-m-d\TH:i')) }}" class="@error('arrival_at') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'arrival_at'])
                </div>
                <div class="field">
                    <label for="seats_total">Всего мест</label>
                    <input id="seats_total" type="number" min="1" max="999" name="seats_total" value="{{ old('seats_total', $editingFlight->seats_total ?? 1) }}" class="@error('seats_total') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'seats_total'])
                </div>
                <div class="field">
                    <label for="seats_available">Свободно</label>
                    <input id="seats_available" type="number" min="0" max="999" name="seats_available" value="{{ old('seats_available', $editingFlight->seats_available ?? 1) }}" class="@error('seats_available') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'seats_available'])
                </div>
                <div class="field">
                    <label for="base_price">Базовая цена</label>
                    <input id="base_price" type="number" step="0.01" min="0" max="999999.99" name="base_price" value="{{ old('base_price', $editingFlight->base_price ?? 0) }}" class="@error('base_price') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'base_price'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingFlight ? 'Сохранить' : 'Добавить' }}</button>
                @if ($editingFlight)
                    <a class="button" href="{{ route('flights.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Поиск</h2>
        <form method="get" action="{{ route('flights.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Номер, город или авиакомпания</label>
                    <input id="search" name="search" value="{{ $search }}">
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('flights.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица №2: рейсы</h2>
        <table>
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Авиакомпания</th>
                    <th>Маршрут</th>
                    <th>Вылет</th>
                    <th>Прилет</th>
                    <th>Места</th>
                    <th>Цена</th>
                    <th>Билетов</th>
                    <th>Действия</th>
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
                        <td>{{ $flight->tickets->count() }}</td>
                        <td>
                            <div class="table-actions">
                                <a class="button small" href="{{ route('flights.edit', $flight) }}">Изменить</a>
                                <form class="inline" method="post" action="{{ route('flights.destroy', $flight) }}" onsubmit="return confirm('Удалить рейс и связанные билеты?')">
                                    @csrf
                                    @method('delete')
                                    <button class="button small danger" type="submit">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Рейсы не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <script>
        (() => {
            const airlineSelect = document.getElementById('airline_id');
            const flightNumberPreview = document.getElementById('flight_number_preview');
            const flightId = @json($editingFlight->id ?? $nextFlightId);

            if (!airlineSelect || !flightNumberPreview) {
                return;
            }

            const renderFlightNumber = () => {
                const code = airlineSelect.selectedOptions[0]?.dataset.code || 'КОД';
                flightNumberPreview.value = `${code}-${flightId}`;
            };

            airlineSelect.addEventListener('change', renderFlightNumber);
            renderFlightNumber();
        })();
    </script>
@endsection
