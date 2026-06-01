@extends('layouts.app')

@section('title', 'Баланс')

@section('content')
    <div class="page-title">
        <div>
            <h1>Баланс аккаунта</h1>
            <p class="muted">Средства используются для покупки билетов в каталоге рейсов.</p>
        </div>
        <a class="button primary" href="{{ route('catalog.index') }}">Выбрать рейс</a>
    </div>

    <section class="section">
        <h2>Текущий баланс</h2>
        <div class="summary">
            <span>{{ number_format((float) auth()->user()->balance, 2, ',', ' ') }} руб.</span>
        </div>
        <form method="post" action="{{ route('account.balance.top-up') }}">
            @csrf
            <div class="grid">
                <div class="field">
                    <label for="amount">Сумма пополнения</label>
                    <input id="amount" type="number" step="0.01" min="100" max="500000" name="amount" value="{{ old('amount', 5000) }}" class="@error('amount') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'amount'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Пополнить</button>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Операции по балансу</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Сумма</th>
                        <th>Билет</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $transaction->typeLabel() }}</td>
                            <td>{{ number_format((float) $transaction->amount, 2, ',', ' ') }} руб.</td>
                            <td>
                                @if ($transaction->ticket)
                                    {{ $transaction->ticket->flight->flight_number ?? 'Билет #'.$transaction->ticket_id }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Операций пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
