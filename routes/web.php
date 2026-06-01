<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('catalog/{flight}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'not_blocked'])->group(function (): void {
    Route::get('account/balance', [AccountController::class, 'balance'])->name('account.balance');
    Route::post('account/balance', [AccountController::class, 'topUp'])->name('account.balance.top-up');
    Route::get('account/tickets', [AccountController::class, 'tickets'])->name('account.tickets');
    Route::post('catalog/{flight}/buy', [AccountController::class, 'buy'])->name('catalog.buy');
    Route::patch('account/tickets/{ticket}/cancel', [AccountController::class, 'cancel'])->name('account.tickets.cancel');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('admin/help', [GuideController::class, 'index'])->name('admin.help');
    Route::get('admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::patch('admin/users/{user}/block', [UserController::class, 'block'])->name('admin.users.block');
    Route::patch('admin/users/{user}/unblock', [UserController::class, 'unblock'])->name('admin.users.unblock');
    Route::patch('admin/users/{user}/balance', [UserController::class, 'adjustBalance'])->name('admin.users.balance');
    Route::resource('admin/promo-codes', PromoCodeController::class)
        ->names('admin.promo-codes')
        ->only(['index', 'store', 'edit', 'update', 'destroy']);

    Route::resource('airlines', AirlineController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('flights', FlightController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::resource('tickets', TicketController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
});
