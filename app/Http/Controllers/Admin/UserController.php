<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('tickets')
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->string('search');
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($request->query('status') === 'blocked', fn ($query) => $query->whereNotNull('blocked_at'))
                ->when($request->query('status') === 'active', fn ($query) => $query->whereNull('blocked_at'))
                ->orderBy('role')
                ->orderBy('name')
                ->get(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function block(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Администраторов блокировать нельзя.']);
        }

        $user->update(['blocked_at' => now()]);

        return back()->with('success', 'Пользователь заблокирован.');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update(['blocked_at' => null]);

        return back()->with('success', 'Пользователь разблокирован.');
    }

    public function adjustBalance(Request $request, User $user): RedirectResponse
    {
        $errorBag = "balance_{$user->id}";

        $data = $request->validateWithBag($errorBag, [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:-500000', 'max:500000', 'not_in:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $data, $errorBag): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $amount = (float) $data['amount'];

            if ($amount < 0 && abs($amount) > (float) $lockedUser->balance) {
                throw ValidationException::withMessages(['amount' => 'Нельзя списать больше текущего баланса.'])
                    ->errorBag($errorBag);
            }

            $lockedUser->increment('balance', $amount);
            $lockedUser->balanceTransactions()->create([
                'type' => BalanceTransaction::TYPE_ADMIN,
                'amount' => $amount,
                'description' => $data['description'] ?: 'Корректировка баланса администратором',
            ]);
        });

        return back()->with('success', 'Баланс пользователя обновлен.');
    }
}
