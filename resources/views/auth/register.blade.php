@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="page-title">
        <div>
            <h1>Регистрация покупателя</h1>
            <p class="muted">После регистрации пополните баланс и купите билет из каталога рейсов.</p>
        </div>
    </div>

    <section class="section">
        <h2>Новый аккаунт</h2>
        <form method="post" action="{{ route('register.store') }}">
            @csrf
            <div class="grid">
                <div class="field wide">
                    <label for="name">Имя</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="@error('name') is-invalid @enderror" maxlength="255" required autofocus>
                    @include('partials.field-error', ['field' => 'name'])
                </div>
                <div class="field wide">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" maxlength="255" required>
                    @include('partials.field-error', ['field' => 'email'])
                </div>
                <div class="field wide">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" name="password" class="@error('password') is-invalid @enderror" required>
                    @include('partials.field-error', ['field' => 'password'])
                </div>
                <div class="field wide">
                    <label for="password_confirmation">Повтор пароля</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Зарегистрироваться</button>
                <a class="button" href="{{ route('login') }}">Уже есть аккаунт</a>
            </div>
        </form>
    </section>
@endsection
