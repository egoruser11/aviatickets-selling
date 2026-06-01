@extends('layouts.app')

@section('title', 'Покупка билета')

@section('content')
    @php
        $oldSeats = collect(old('seat_numbers', []))->filter()->values();
        $selectSlots = 6;
    @endphp

    <div class="page-title">
        <div>
            <h1>{{ $flight->flight_number }} · {{ $flight->origin }} - {{ $flight->destination }}</h1>
            <p class="muted">{{ $flight->airline->name }} · вылет {{ $flight->departure_at->format('d.m.Y H:i') }}</p>
        </div>
        <a class="button" href="{{ route('catalog.index') }}">К каталогу</a>
    </div>

    <div class="split">
        <section class="section">
            <h2>Покупка билета</h2>
            <div class="summary">
                <span>Цена: {{ number_format((float) $flight->base_price, 2, ',', ' ') }} руб.</span>
                <span>Свободно: {{ $flight->seats_available }} из {{ $flight->seats_total }}</span>
                @auth
                    @if (! auth()->user()->isAdmin())
                        <span>Баланс: {{ number_format((float) auth()->user()->balance, 2, ',', ' ') }} руб.</span>
                    @endif
                @endauth
            </div>

            @guest
                <div class="notice">
                    Для покупки билета войдите в аккаунт или зарегистрируйтесь.
                    <div class="actions">
                        <a class="button primary" href="{{ route('login') }}">Войти</a>
                        <a class="button" href="{{ route('register') }}">Регистрация</a>
                    </div>
                </div>
            @else
                @if (auth()->user()->isAdmin())
                    <div class="notice">Администратор создает и правит билеты в разделе управления билетами.</div>
                @else
                    <form method="post" action="{{ route('catalog.buy', $flight) }}">
                        @csrf
                        <div class="grid">
                            <div class="field wide">
                                <label for="passenger_name">Пассажир</label>
                                <input id="passenger_name" name="passenger_name" value="{{ old('passenger_name', auth()->user()->name) }}" class="@error('passenger_name') is-invalid @enderror" maxlength="255" required>
                                @include('partials.field-error', ['field' => 'passenger_name'])
                            </div>
                            <div class="field wide">
                                <label for="passenger_email">Email</label>
                                <input id="passenger_email" type="email" name="passenger_email" value="{{ old('passenger_email', auth()->user()->email) }}" class="@error('passenger_email') is-invalid @enderror" maxlength="255" required>
                                @include('partials.field-error', ['field' => 'passenger_email'])
                            </div>
                            @for ($slot = 0; $slot < $selectSlots; $slot++)
                                <div class="field">
                                    <label for="seat_numbers_{{ $slot }}">Место {{ $slot + 1 }}</label>
                                    <select id="seat_numbers_{{ $slot }}" name="seat_numbers[]" class="@error('seat_numbers') is-invalid @enderror @error('seat_numbers.'.$slot) is-invalid @enderror" @required($slot === 0)>
                                        <option value="">{{ $slot === 0 ? 'Выберите место' : 'Не выбирать' }}</option>
                                        @foreach ($availableSeats as $seat)
                                            <option value="{{ $seat }}" @selected($oldSeats->get($slot) === $seat)>{{ $seat }}</option>
                                        @endforeach
                                    </select>
                                    @error('seat_numbers.'.$slot)
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endfor
                            <div class="field wide">
                                <label for="promo_code">Промокод</label>
                                <input id="promo_code" name="promo_code" value="{{ old('promo_code') }}" class="@error('promo_code') is-invalid @enderror" maxlength="32" placeholder="Введите код промокода">
                                @include('partials.field-error', ['field' => 'promo_code'])
                            </div>
                        </div>
                        @include('partials.field-error', ['field' => 'seat_numbers'])
                        @include('partials.field-error', ['field' => 'flight'])
                        @include('partials.field-error', ['field' => 'balance'])
                        <div class="actions">
                            <button class="button primary" type="submit" @disabled($flight->seats_available < 1 || $availableSeats->isEmpty())>Купить выбранные места</button>
                            <a class="button" href="{{ route('account.balance') }}">Пополнить баланс</a>
                        </div>
                    </form>
                @endif
            @endguest
        </section>

        <section class="section">
            <h2>Занятые места</h2>
            @if ($busySeats->isEmpty())
                <p class="muted">На этом рейсе пока нет занятых мест.</p>
            @else
                <div class="summary">
                    @foreach ($busySeats as $seat)
                        <span>{{ $seat }}</span>
                    @endforeach
                </div>
            @endif

            @if ($promoCodes->isNotEmpty())
                <h2 class="stack-gap">Промокоды</h2>
                <div class="summary">
                    @foreach ($promoCodes as $promoCode)
                        <span>{{ $promoCode->code }} · {{ $promoCode->type === \App\Models\PromoCode::TYPE_PERCENT ? rtrim(rtrim(number_format((float) $promoCode->value, 2, ',', ' '), '0'), ',').'%' : number_format((float) $promoCode->value, 2, ',', ' ').' руб.' }}</span>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
