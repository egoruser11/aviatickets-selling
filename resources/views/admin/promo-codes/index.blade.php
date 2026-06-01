@extends('layouts.app')

@section('title', 'Промокоды')

@section('content')
    <div class="page-title">
        <div>
            <h1>Промокоды</h1>
            <p class="muted">Базовые скидки для покупки одного или нескольких билетов за раз.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">К панели</a>
    </div>

    <section class="section">
        <h2>{{ $editingPromoCode ? 'Изменение промокода' : 'Новый промокод' }}</h2>
        <form method="post" action="{{ $editingPromoCode ? route('admin.promo-codes.update', $editingPromoCode) : route('admin.promo-codes.store') }}">
            @csrf
            @if ($editingPromoCode)
                @method('put')
            @endif
            <div class="grid">
                <div class="field">
                    <label for="code">Код</label>
                    <input id="code" name="code" value="{{ old('code', $editingPromoCode->code ?? '') }}" class="@error('code') is-invalid @enderror" maxlength="32" required>
                    @include('partials.field-error', ['field' => 'code'])
                </div>
                <div class="field wide">
                    <label for="name">Описание</label>
                    <input id="name" name="name" value="{{ old('name', $editingPromoCode->name ?? '') }}" class="@error('name') is-invalid @enderror" maxlength="255">
                    @include('partials.field-error', ['field' => 'name'])
                </div>
                <div class="field">
                    <label for="type">Тип скидки</label>
                    <select id="type" name="type" class="@error('type') is-invalid @enderror" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $editingPromoCode->type ?? \App\Models\PromoCode::TYPE_PERCENT) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['field' => 'type'])
                </div>
                <div class="field">
                    <label for="value">Размер скидки</label>
                    <input id="value" type="number" step="0.01" min="0.01" max="{{ old('type', $editingPromoCode->type ?? \App\Models\PromoCode::TYPE_PERCENT) === \App\Models\PromoCode::TYPE_PERCENT ? \App\Models\PromoCode::MAX_PERCENT_VALUE : \App\Models\PromoCode::MAX_FIXED_VALUE }}" name="value" value="{{ old('value', $editingPromoCode->value ?? 10) }}" class="@error('value') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'value'])
                </div>
                <div class="field">
                    <label for="max_uses">Лимит использований</label>
                    <input id="max_uses" type="number" min="1" max="{{ \App\Models\PromoCode::MAX_USES }}" name="max_uses" value="{{ old('max_uses', $editingPromoCode->max_uses ?? '') }}" class="@error('max_uses') is-invalid @enderror">
                    @include('partials.field-error', ['field' => 'max_uses'])
                </div>
                <div class="field">
                    <label for="starts_at">Начало</label>
                    <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($editingPromoCode?->starts_at)->format('Y-m-d\TH:i')) }}" class="@error('starts_at') is-invalid @enderror">
                    @include('partials.field-error', ['field' => 'starts_at'])
                </div>
                <div class="field">
                    <label for="expires_at">Окончание</label>
                    <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($editingPromoCode?->expires_at)->format('Y-m-d\TH:i')) }}" class="@error('expires_at') is-invalid @enderror">
                    @include('partials.field-error', ['field' => 'expires_at'])
                </div>
                <div class="field">
                    <label>
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editingPromoCode->is_active ?? true)) style="width:auto; min-height:auto;">
                        Активен
                    </label>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingPromoCode ? 'Сохранить' : 'Создать' }}</button>
                @if ($editingPromoCode)
                    <a class="button" href="{{ route('admin.promo-codes.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Поиск</h2>
        <form method="get" action="{{ route('admin.promo-codes.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Код или описание</label>
                    <input id="search" name="search" value="{{ $search }}">
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('admin.promo-codes.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Список промокодов</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Код</th>
                        <th>Описание</th>
                        <th>Скидка</th>
                        <th>Статус</th>
                        <th>Использований</th>
                        <th>Период</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promoCodes as $promoCode)
                        <tr>
                            <td><strong>{{ $promoCode->code }}</strong></td>
                            <td>{{ $promoCode->name ?: '-' }}</td>
                            <td>
                                {{ $promoCode->typeLabel() }} ·
                                {{ $promoCode->type === \App\Models\PromoCode::TYPE_PERCENT ? rtrim(rtrim(number_format((float) $promoCode->value, 2, ',', ' '), '0'), ',').'%' : number_format((float) $promoCode->value, 2, ',', ' ').' руб.' }}
                            </td>
                            <td>
                                @if ($promoCode->isUsable())
                                    <span class="badge success">Работает</span>
                                @else
                                    <span class="badge danger">Недоступен</span>
                                @endif
                            </td>
                            <td>{{ $promoCode->used_count }}{{ $promoCode->max_uses ? ' из '.$promoCode->max_uses : '' }}</td>
                            <td>
                                {{ optional($promoCode->starts_at)->format('d.m.Y H:i') ?: 'сейчас' }}
                                -
                                {{ optional($promoCode->expires_at)->format('d.m.Y H:i') ?: 'без срока' }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a class="button small" href="{{ route('admin.promo-codes.edit', $promoCode) }}">Изменить</a>
                                    <form class="inline" method="post" action="{{ route('admin.promo-codes.destroy', $promoCode) }}" onsubmit="return confirm('Удалить промокод?')">
                                        @csrf
                                        @method('delete')
                                        <button class="button small danger" type="submit">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Промокоды не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        const promoType = document.getElementById('type');
        const promoValue = document.getElementById('value');
        const promoValueLimits = {
            percent: {{ \App\Models\PromoCode::MAX_PERCENT_VALUE }},
            fixed: {{ \App\Models\PromoCode::MAX_FIXED_VALUE }},
        };

        promoType.addEventListener('change', () => {
            promoValue.max = promoValueLimits[promoType.value];
        });
    </script>
@endsection
