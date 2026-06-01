@extends('layouts.app')

@section('title', 'Главная')

@section('content')
    <div class="page-title">
        <div>
            <h1>Система продажи авиабилетов</h1>
            <p class="muted">Курсовая работа: PostgreSQL, роли пользователя и администратора, покупка билетов с балансом аккаунта.</p>
        </div>
    </div>

    <section class="section">
        <h2>Сводка</h2>
        <div class="summary">
            <span>Авиакомпаний: {{ $airlinesCount }}</span>
            <span>Рейсов: {{ $flightsCount }}</span>
            <span>Билетов: {{ $ticketsCount }}</span>
            <span>Покупателей: {{ $usersCount }}</span>
        </div>
    </section>

    <section class="menu-grid">
        <a class="menu-link" href="{{ route('catalog.index') }}">
            <strong>Каталог рейсов</strong>
            Поиск доступных рейсов и покупка билетов для обычного пользователя.
        </a>
        @guest
            <a class="menu-link" href="{{ route('login') }}">
                <strong>Вход</strong>
                Авторизация покупателя или администратора.
            </a>
            <a class="menu-link" href="{{ route('register') }}">
                <strong>Регистрация</strong>
                Создание аккаунта покупателя с личным балансом.
            </a>
        @else
            @if (auth()->user()->isAdmin())
                <a class="menu-link" href="{{ route('admin.dashboard') }}">
                    <strong>Администрирование</strong>
                    Управление рейсами, билетами, пользователями и отчетами.
                </a>
                <a class="menu-link" href="{{ route('admin.help') }}">
                    <strong>Инструкция</strong>
                    Памятка по созданию билетов, блокировке пользователей и балансу.
                </a>
            @else
                <a class="menu-link" href="{{ route('account.balance') }}">
                    <strong>Баланс</strong>
                    Пополнение счета и история операций.
                </a>
                <a class="menu-link" href="{{ route('account.tickets') }}">
                    <strong>Мои билеты</strong>
                    Купленные билеты, статусы и отмена.
                </a>
            @endif
        @endguest
    </section>
@endsection
