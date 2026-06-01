@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <div class="page-title">
        <div>
            <h1>Пользователи</h1>
            <p class="muted">Администратор может блокировать покупателей и корректировать баланс аккаунта.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">К панели</a>
    </div>

    <section class="section">
        <h2>Поиск</h2>
        <form method="get" action="{{ route('admin.users.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Имя или email</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" name="status">
                        <option value="">Все</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Активные</option>
                        <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Заблокированные</option>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('admin.users.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Список аккаунтов</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Баланс</th>
                        <th>Билетов</th>
                        <th>Корректировка</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}<br><span class="muted">{{ $user->email }}</span></td>
                            <td>{{ $user->isAdmin() ? 'Администратор' : 'Покупатель' }}</td>
                            <td>
                                @if ($user->isBlocked())
                                    <span class="badge danger">Заблокирован</span>
                                @else
                                    <span class="badge success">Активен</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $user->balance, 2, ',', ' ') }} руб.</td>
                            <td>{{ $user->tickets_count }}</td>
                            <td>
                                @if (! $user->isAdmin())
                                    <form method="post" action="{{ route('admin.users.balance', $user) }}">
                                        @csrf
                                        @method('patch')
                                        <div class="grid">
                                            <div class="field">
                                                <label for="amount_{{ $user->id }}">Сумма</label>
                                                <input id="amount_{{ $user->id }}" type="number" step="0.01" min="-500000" max="500000" name="amount" class="@error('amount', 'balance_'.$user->id) is-invalid @enderror" required>
                                                @include('partials.field-error', ['field' => 'amount', 'bag' => 'balance_'.$user->id])
                                            </div>
                                            <div class="field wide">
                                                <label for="description_{{ $user->id }}">Комментарий</label>
                                                <input id="description_{{ $user->id }}" name="description" class="@error('description', 'balance_'.$user->id) is-invalid @enderror" maxlength="255">
                                                @include('partials.field-error', ['field' => 'description', 'bag' => 'balance_'.$user->id])
                                            </div>
                                        </div>
                                        <button class="button small" type="submit">Применить</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($user->isAdmin())
                                    -
                                @elseif ($user->isBlocked())
                                    <form method="post" action="{{ route('admin.users.unblock', $user) }}">
                                        @csrf
                                        @method('patch')
                                        <button class="button small" type="submit">Разблокировать</button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('admin.users.block', $user) }}" onsubmit="return confirm('Заблокировать пользователя?')">
                                        @csrf
                                        @method('patch')
                                        <button class="button small danger" type="submit">Заблокировать</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Пользователи не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
