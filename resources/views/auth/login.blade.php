@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <div class="page-title">
        <div>
            <h1>Вход в систему</h1>
            <p class="muted">Пользователь покупает билеты, администратор управляет справочниками, рейсами и аккаунтами.</p>
        </div>
    </div>

    <section class="section">
        <h2>Авторизация</h2>
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="grid">
                <div class="field wide">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" maxlength="255" required autofocus>
                    @include('partials.field-error', ['field' => 'email'])
                </div>
                <div class="field wide">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" name="password" class="@error('password') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'password'])
                </div>
                <div class="field full">
                    <label>
                        <input type="checkbox" name="remember" value="1" style="width:auto; min-height:auto;">
                        Запомнить вход
                    </label>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Войти</button>
                <a class="button" href="{{ route('register') }}">Создать аккаунт</a>
            </div>
        </form>
    </section>
@endsection
