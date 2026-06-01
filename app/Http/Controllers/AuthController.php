<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'catalog.index');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => str($request->input('email', ''))->trim()->lower()->toString(),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Неверный email или пароль.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.dashboard') : route('catalog.index'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'catalog.index');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'email' => str($request->input('email', ''))->trim()->lower()->toString(),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_USER,
            'balance' => 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.balance')->with('success', 'Аккаунт создан. Пополните баланс и можно покупать билеты.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Вы вышли из системы.');
    }
}
