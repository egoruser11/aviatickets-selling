<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Система продажи авиабилетов')</title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --ink: #172033;
            --muted: #637083;
            --line: #d9e0ea;
            --brand: #0f766e;
            --brand-strong: #0b5f59;
            --danger: #b42318;
            --soft: #eef7f6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            letter-spacing: 0;
        }

        a {
            color: var(--brand-strong);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .topbar {
            background: var(--panel);
            border-bottom: 1px solid var(--line);
        }

        .topbar-inner,
        main {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 20px;
            min-height: 72px;
        }

        .brand {
            color: var(--ink);
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .nav a,
        .nav button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--panel);
            color: var(--ink);
            padding: 7px 11px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--panel);
            color: var(--ink);
            padding: 8px 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .nav a.active,
        .button.primary {
            background: var(--brand);
            border-color: var(--brand);
            color: #ffffff;
        }

        .button.danger {
            color: var(--danger);
        }

        .nav-form {
            margin: 0;
        }

        .account-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .user-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #f8fafc;
            color: var(--muted);
            padding: 7px 11px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .nav button.logout-button {
            border-color: transparent;
            background: #f8fafc;
            color: var(--muted);
        }

        .nav button.logout-button:hover {
            color: var(--danger);
        }

        .admin-strip {
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .admin-strip-inner {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
        }

        .admin-strip-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            margin-right: 4px;
            white-space: nowrap;
        }

        .admin-strip a {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            border-radius: 6px;
            color: var(--ink);
            padding: 5px 9px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-strip a.active {
            background: var(--brand);
            color: #ffffff;
        }

        .button.small {
            min-height: 32px;
            padding: 6px 10px;
            font-size: 14px;
        }

        main {
            padding: 28px 0 42px;
        }

        .page-title {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
        }

        h2 {
            margin: 0 0 14px;
            font-size: 19px;
            line-height: 1.25;
        }

        .muted {
            color: var(--muted);
        }

        .section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }

        .field {
            grid-column: span 3;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field.wide {
            grid-column: span 6;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #ffffff;
            color: var(--ink);
            padding: 8px 10px;
            font: inherit;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        .is-invalid {
            border-color: var(--danger);
            background: #fffafa;
        }

        .field-error {
            color: var(--danger);
            font-size: 13px;
            line-height: 1.35;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 11px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f0f4f8;
            color: #27364b;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .inline {
            display: inline;
        }

        .flash,
        .errors {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .flash {
            background: var(--soft);
            border: 1px solid #b9dfda;
        }

        .errors {
            background: #fff4f2;
            border: 1px solid #ffd0ca;
            color: var(--danger);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #eef2f7;
            color: #27364b;
            padding: 4px 9px;
            font-size: 13px;
            font-weight: 700;
        }

        .badge.danger {
            background: #fff4f2;
            color: var(--danger);
        }

        .badge.success {
            background: var(--soft);
            color: var(--brand-strong);
        }

        .notice {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
        }

        .split {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.6fr);
            gap: 18px;
        }

        .guide-list {
            margin: 0;
            padding-left: 20px;
        }

        .guide-list li {
            margin-bottom: 8px;
        }

        .stack-gap {
            margin-top: 20px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
        }

        .menu-link {
            display: block;
            min-height: 118px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            padding: 18px;
            color: var(--ink);
        }

        .menu-link strong {
            display: block;
            margin-bottom: 8px;
            font-size: 17px;
        }

        .scroll-table {
            max-height: 430px;
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .scroll-table table {
            min-width: 980px;
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }

        .summary span {
            background: #eef2f7;
            border-radius: 999px;
            padding: 8px 12px;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .topbar-inner,
            .page-title {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .split {
                grid-template-columns: 1fr;
            }

            .field,
            .field.wide {
                grid-column: span 6;
            }
        }

        @media (max-width: 640px) {
            .topbar-inner,
            main,
            .admin-strip-inner {
                width: min(100% - 20px, 1180px);
            }

            .menu-grid,
            .grid {
                grid-template-columns: 1fr;
            }

            .field,
            .field.wide,
            .field.full {
                grid-column: 1;
            }

            .button,
            .nav a,
            .nav button {
                width: 100%;
            }

            .nav-form {
                width: 100%;
            }

            .account-actions {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ route('home') }}">Продажа авиабилетов</a>
            <nav class="nav" aria-label="Основная навигация">
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Главная</a>
                <a class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Каталог</a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Панель</a>
                        <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Отчеты</a>
                        <a class="{{ request()->routeIs('admin.help') ? 'active' : '' }}" href="{{ route('admin.help') }}">Инструкция</a>
                    @else
                        <a class="{{ request()->routeIs('account.balance') ? 'active' : '' }}" href="{{ route('account.balance') }}">Баланс</a>
                        <a class="{{ request()->routeIs('account.tickets') ? 'active' : '' }}" href="{{ route('account.tickets') }}">Мои билеты</a>
                    @endif
                @endauth
            </nav>
            <div class="account-actions nav">
                @auth
                    <span class="user-chip">{{ auth()->user()->isAdmin() ? 'Админ' : number_format((float) auth()->user()->balance, 2, ',', ' ').' руб.' }}</span>
                    <form class="nav-form" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">Выйти</button>
                    </form>
                @else
                    <a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Вход</a>
                    <a class="{{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Регистрация</a>
                @endauth
            </div>
        </div>
        @auth
            @if (auth()->user()->isAdmin())
                <div class="admin-strip">
                    <div class="admin-strip-inner" aria-label="Администрирование">
                        <span class="admin-strip-label">Управление:</span>
                        <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Пользователи</a>
                        <a class="{{ request()->routeIs('airlines.*') ? 'active' : '' }}" href="{{ route('airlines.index') }}">Авиакомпании</a>
                        <a class="{{ request()->routeIs('flights.*') ? 'active' : '' }}" href="{{ route('flights.index') }}">Рейсы CRUD</a>
                        <a class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">Билеты</a>
                        <a class="{{ request()->routeIs('admin.promo-codes.*') ? 'active' : '' }}" href="{{ route('admin.promo-codes.index') }}">Промокоды</a>
                    </div>
                </div>
            @endif
        @endauth
    </header>

    <main>
        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="errors">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Проверьте поля формы.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
