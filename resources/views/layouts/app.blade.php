<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Система продажи авиабилетов')</title>
    <style>
        :root {
            --bg: #f3f3f4;
            --panel: #ffffff;
            --ink: #18181b;
            --muted: #68686f;
            --line: #d6d6da;
            --brand: #c1121f;
            --brand-strong: #950f18;
            --danger: #b91c1c;
            --soft: #fdf0f1;
            --dark: #151518;
            --dark-soft: #242428;
            --success: #166534;
            --success-soft: #ecfdf3;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, Helvetica, sans-serif;
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
            background: var(--dark);
            border-bottom: 1px solid #34343a;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.16);
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
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }

        .brand::before {
            width: 8px;
            height: 28px;
            background: var(--brand);
            content: "";
        }

        .brand:hover {
            color: #ffffff;
            text-decoration: none;
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
            border: 1px solid #45454c;
            border-radius: 4px;
            background: var(--dark-soft);
            color: #f4f4f5;
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
            border-radius: 4px;
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

        .nav a:hover,
        .nav button:hover {
            border-color: #707078;
            text-decoration: none;
        }

        .button:hover {
            border-color: var(--brand);
            color: var(--brand-strong);
            text-decoration: none;
        }

        .button.primary:hover {
            background: var(--brand-strong);
            border-color: var(--brand-strong);
            color: #ffffff;
        }

        .button.danger {
            color: var(--danger);
            border-color: #fecaca;
            background: #fff5f5;
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
            border: 1px solid #494950;
            border-radius: 999px;
            background: #29292e;
            color: #f4f4f5;
            padding: 7px 11px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .nav button.logout-button {
            border-color: #494950;
            background: #29292e;
            color: #f4f4f5;
        }

        .nav button.logout-button:hover {
            border-color: var(--brand);
            background: var(--brand);
            color: #ffffff;
        }

        .admin-strip {
            border-top: 1px solid #34343a;
            border-bottom: 1px solid #34343a;
            background: #202024;
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
            color: #a1a1aa;
            font-size: 13px;
            font-weight: 700;
            margin-right: 4px;
            white-space: nowrap;
        }

        .admin-strip a {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            border-radius: 4px;
            color: #f4f4f5;
            padding: 5px 9px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-strip a.active {
            background: var(--brand);
            color: #ffffff;
        }

        .admin-strip a:hover {
            background: #34343a;
            color: #ffffff;
            text-decoration: none;
        }

        .admin-strip a.active:hover {
            background: var(--brand-strong);
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
            border-top: 3px solid var(--dark);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 8px 20px rgba(24, 24, 27, 0.05);
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
            border-radius: 4px;
            background: #ffffff;
            color: var(--ink);
            padding: 8px 10px;
            font: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--brand);
            outline: 3px solid rgba(193, 18, 31, 0.14);
            outline-offset: 1px;
        }

        input[readonly],
        input:disabled,
        select:disabled,
        textarea[readonly],
        textarea:disabled {
            background: #f0f0f1;
            color: var(--muted);
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
            background: var(--dark);
            color: #ffffff;
            font-size: 13px;
        }

        tbody tr:hover {
            background: #fff7f7;
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
            border-radius: 4px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .flash {
            background: var(--success-soft);
            border: 1px solid #bbf7d0;
            color: var(--success);
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
            background: #eeeeef;
            color: #3f3f46;
            padding: 4px 9px;
            font-size: 13px;
            font-weight: 700;
        }

        .badge.danger {
            background: #fff4f2;
            color: var(--danger);
        }

        .badge.success {
            background: var(--success-soft);
            color: var(--success);
        }

        .notice {
            background: #fafafa;
            border: 1px solid var(--line);
            border-left: 4px solid var(--brand);
            border-radius: 4px;
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
            border-radius: 4px;
            background: var(--panel);
            padding: 18px;
            color: var(--ink);
            box-shadow: 0 8px 20px rgba(24, 24, 27, 0.05);
        }

        .menu-link:hover {
            border-color: var(--brand);
            color: var(--brand-strong);
            text-decoration: none;
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
            border-radius: 4px;
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
            border-left: 3px solid var(--brand);
            background: #eeeeef;
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
