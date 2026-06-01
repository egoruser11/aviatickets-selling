@extends('layouts.app')

@section('title', 'Форма 5 - билеты')

@section('content')
    @php
        $nextDirection = $direction === 'asc' ? 'desc' : 'asc';
        $sortLink = fn (string $key) => route('tickets.index', ['sort' => $key, 'direction' => $sort === $key ? $nextDirection : 'asc']);
        $currentFlightId = (int) old('flight_id', $editingTicket->flight_id ?? optional($flights->first())->id);
        $currentSeatOptions = $seatOptionsByFlight[$currentFlightId] ?? [];
    @endphp

    <div class="page-title">
        <div>
            <h1>Форма 5 · Билеты</h1>
            <p class="muted">Таблица №3 с внешним ключом на рейс, сортировкой и прокруткой.</p>
        </div>
    </div>

    <section class="section">
        <h2>{{ $editingTicket ? 'Изменение билета' : 'Добавление билета' }}</h2>
        <form method="post" action="{{ $editingTicket ? route('tickets.update', $editingTicket) : route('tickets.store') }}">
            @csrf
            @if ($editingTicket)
                @method('put')
            @endif
            <div class="grid">
                <div class="field wide">
                    <label for="flight_id">Рейс</label>
                    <select id="flight_id" name="flight_id" class="@error('flight_id') is-invalid @enderror" required>
                        @foreach ($flights as $flight)
                            <option value="{{ $flight->id }}" @selected((int) old('flight_id', $editingTicket->flight_id ?? 0) === $flight->id)>
                                {{ $flight->flight_number }} · {{ $flight->origin }} - {{ $flight->destination }} · {{ $flight->airline->name }}
                            </option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'flight_id'])
                </div>
                <div class="field wide">
                    <label for="user_id">Email покупателя</label>
                    <select id="user_id" name="user_id" class="@error('user_id') is-invalid @enderror" required>
                        <option value="">Выберите email</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" data-name="{{ $user->name }}" @selected((int) old('user_id', $editingTicket->user_id ?? 0) === $user->id)>
                                {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'user_id'])
                </div>
                <div class="field wide">
                    <label for="passenger_name">Пассажир</label>
                    <input id="passenger_name" value="{{ old('passenger_name', $editingTicket->passenger_name ?? '') }}" readonly>
                </div>
                <div class="field">
                    <label for="seat_number">Место</label>
                    <select id="seat_number" name="seat_number" class="@error('seat_number') is-invalid @enderror" required>
                        <option value="">Выберите место</option>
                        @foreach ($currentSeatOptions as $seat)
                            <option value="{{ $seat }}" @selected(old('seat_number', $editingTicket->seat_number ?? '') === $seat)>{{ $seat }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'seat_number'])
                </div>
                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" name="status" class="@error('status') is-invalid @enderror" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $editingTicket->status ?? 'booked') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'status'])
                </div>
                <div class="field">
                    <label for="price">Цена</label>
                    <input id="price" type="number" step="0.01" min="0" max="999999.99" name="price" value="{{ old('price', $editingTicket->price ?? 0) }}" class="@error('price') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'price'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingTicket ? 'Сохранить' : 'Добавить' }}</button>
                @if ($editingTicket)
                    <a class="button" href="{{ route('tickets.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица №3: билеты</h2>
        <div class="summary">
            <span>Сортировка: {{ $directions[$direction] }}</span>
            <span>Записей: {{ $tickets->count() }}</span>
        </div>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('passenger') }}">Пассажир</a></th>
                        <th>Аккаунт</th>
                        <th><a href="{{ $sortLink('flight') }}">Рейс</a></th>
                        <th>Маршрут</th>
                        <th>Email</th>
                        <th>Место</th>
                        <th><a href="{{ $sortLink('status') }}">Статус</a></th>
                        <th><a href="{{ $sortLink('price') }}">Цена</a></th>
                        <th>Скидка</th>
                        <th><a href="{{ $sortLink('purchased') }}">Дата покупки</a></th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->passenger_name }}</td>
                            <td>{{ $ticket->user?->email ?? '-' }}</td>
                            <td>{{ $ticket->flight->flight_number }}</td>
                            <td>{{ $ticket->flight->origin }} - {{ $ticket->flight->destination }}</td>
                            <td>{{ $ticket->passenger_email }}</td>
                            <td>{{ $ticket->seat_number }}</td>
                            <td>{{ $ticket->statusLabel() }}</td>
                            <td>{{ number_format((float) $ticket->price, 2, ',', ' ') }} руб.</td>
                            <td>
                                @if ((float) $ticket->discount_amount > 0)
                                    {{ number_format((float) $ticket->discount_amount, 2, ',', ' ') }} руб.<br>
                                    <span class="muted">{{ $ticket->promoCode?->code ?: '-' }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $ticket->purchased_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="button small" href="{{ route('tickets.edit', $ticket) }}">Изменить</a>
                                    <form class="inline" method="post" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Удалить билет?')">
                                        @csrf
                                        @method('delete')
                                        <button class="button small danger" type="submit">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">Билеты не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        (() => {
            const flightSelect = document.getElementById('flight_id');
            const seatSelect = document.getElementById('seat_number');
            const buyerSelect = document.getElementById('user_id');
            const passengerName = document.getElementById('passenger_name');
            const optionsByFlight = @json($seatOptionsByFlight);
            const selectedSeat = @json(old('seat_number', $editingTicket->seat_number ?? ''));

            if (!flightSelect || !seatSelect || !buyerSelect || !passengerName) {
                return;
            }

            const renderSeats = () => {
                const seats = optionsByFlight[flightSelect.value] || [];
                const current = seatSelect.value || selectedSeat;
                seatSelect.innerHTML = '<option value="">Выберите место</option>';

                seats.forEach((seat) => {
                    const option = document.createElement('option');
                    option.value = seat;
                    option.textContent = seat;
                    option.selected = seat === current;
                    seatSelect.appendChild(option);
                });
            };

            const renderBuyer = () => {
                passengerName.value = buyerSelect.selectedOptions[0]?.dataset.name || '';
            };

            flightSelect.addEventListener('change', renderSeats);
            buyerSelect.addEventListener('change', renderBuyer);
            renderSeats();
            renderBuyer();
        })();
    </script>
@endsection
