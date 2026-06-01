@extends('layouts.app')

@section('title', 'Форма 2 - авиакомпании')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 2 · Авиакомпании</h1>
            <p class="muted">Таблица №1 и связанные записи из таблицы №2.</p>
        </div>
        <a class="button primary" href="{{ route('flights.index') }}">К форме 3</a>
    </div>

    <section class="section">
        <h2>{{ $editingAirline ? 'Изменение авиакомпании' : 'Добавление авиакомпании' }}</h2>
        <form method="post" action="{{ $editingAirline ? route('airlines.update', $editingAirline) : route('airlines.store') }}">
            @csrf
            @if ($editingAirline)
                @method('put')
            @endif
            <div class="grid">
                <div class="field wide">
                    <label for="name">Название</label>
                    <input id="name" name="name" value="{{ old('name', $editingAirline->name ?? '') }}" class="@error('name') is-invalid @enderror" maxlength="255" required>
                    @include('partials.field-error', ['field' => 'name'])
                </div>
                <div class="field">
                    <label for="code">Код</label>
                    <input id="code" name="code" value="{{ old('code', $editingAirline->code ?? '') }}" class="@error('code') is-invalid @enderror" required maxlength="8">
                    @include('partials.field-error', ['field' => 'code'])
                </div>
                <div class="field">
                    <label for="country">Страна</label>
                    <input id="country" name="country" value="{{ old('country', $editingAirline->country ?? '') }}" class="@error('country') is-invalid @enderror" maxlength="255" required>
                    @include('partials.field-error', ['field' => 'country'])
                </div>
                <div class="field wide">
                    <label for="phone">Телефон</label>
                    <input id="phone" name="phone" value="{{ old('phone', $editingAirline->phone ?? '') }}" class="@error('phone') is-invalid @enderror" maxlength="32">
                    @include('partials.field-error', ['field' => 'phone'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingAirline ? 'Сохранить' : 'Добавить' }}</button>
                @if ($editingAirline)
                    <a class="button" href="{{ route('airlines.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Поиск и фильтрация</h2>
        <form method="get" action="{{ route('airlines.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Название, код или страна</label>
                    <input id="search" name="search" value="{{ $search }}">
                </div>
                <div class="field wide">
                    <label for="airline_id">Связанные рейсы</label>
                    <select id="airline_id" name="airline_id">
                        <option value="">Все авиакомпании</option>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline->id }}" @selected(optional($selectedAirline)->id === $airline->id)>
                                {{ $airline->name }} ({{ $airline->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('airlines.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица №1: авиакомпании</h2>
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Код</th>
                    <th>Страна</th>
                    <th>Телефон</th>
                    <th>Рейсов</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($airlines as $airline)
                    <tr>
                        <td>{{ $airline->name }}</td>
                        <td>{{ $airline->code }}</td>
                        <td>{{ $airline->country }}</td>
                        <td>{{ $airline->phone ?: '-' }}</td>
                        <td>{{ $airline->flights_count }}</td>
                        <td>
                            <div class="table-actions">
                                <a class="button small" href="{{ route('airlines.index', ['airline_id' => $airline->id, 'search' => $search]) }}">Рейсы</a>
                                <a class="button small" href="{{ route('airlines.edit', $airline) }}">Изменить</a>
                                <form class="inline" method="post" action="{{ route('airlines.destroy', $airline) }}" onsubmit="return confirm('Удалить авиакомпанию и связанные данные?')">
                                    @csrf
                                    @method('delete')
                                    <button class="button small danger" type="submit">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Записи не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Таблица №2: рейсы{{ $selectedAirline ? ' - '.$selectedAirline->name : '' }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Рейс</th>
                    <th>Авиакомпания</th>
                    <th>Маршрут</th>
                    <th>Вылет</th>
                    <th>Мест</th>
                    <th>Цена</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($linkedFlights as $flight)
                    <tr>
                        <td>{{ $flight->flight_number }}</td>
                        <td>{{ $flight->airline->name }}</td>
                        <td>{{ $flight->origin }} - {{ $flight->destination }}</td>
                        <td>{{ $flight->departure_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $flight->seats_available }} из {{ $flight->seats_total }}</td>
                        <td>{{ number_format((float) $flight->base_price, 2, ',', ' ') }} руб.</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Связанных рейсов нет.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
