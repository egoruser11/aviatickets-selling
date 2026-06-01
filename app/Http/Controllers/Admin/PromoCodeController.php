<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        return $this->view($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePromoCode($request);
        $data['is_active'] = $request->boolean('is_active');

        PromoCode::query()->create($data);

        return to_route('admin.promo-codes.index')->with('success', 'Промокод создан.');
    }

    public function edit(Request $request, PromoCode $promoCode): View
    {
        return $this->view($request, $promoCode);
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $data = $this->validatePromoCode($request, $promoCode);
        $data['is_active'] = $request->boolean('is_active');

        $promoCode->update($data);

        return to_route('admin.promo-codes.index')->with('success', 'Промокод обновлен.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();

        return to_route('admin.promo-codes.index')->with('success', 'Промокод удален.');
    }

    private function view(Request $request, ?PromoCode $editingPromoCode = null): View
    {
        return view('admin.promo-codes.index', [
            'editingPromoCode' => $editingPromoCode,
            'promoCodes' => PromoCode::query()
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->string('search');
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orderByDesc('is_active')
                ->orderBy('code')
                ->get(),
            'search' => $request->query('search', ''),
            'types' => PromoCode::TYPES,
        ]);
    }

    private function validatePromoCode(Request $request, ?PromoCode $promoCode = null): array
    {
        $request->merge([
            'code' => str($request->input('code', ''))->trim()->upper()->toString(),
            'name' => $request->filled('name') ? trim((string) $request->input('name')) : null,
        ]);

        $expiresAtRules = ['nullable', 'date'];

        if ($request->filled('starts_at')) {
            $expiresAtRules[] = 'after:starts_at';
        }

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('promo_codes', 'code')->ignore($promoCode),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(array_keys(PromoCode::TYPES))],
            'value' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                Rule::when(
                    $request->input('type') === PromoCode::TYPE_PERCENT,
                    'max:'.PromoCode::MAX_PERCENT_VALUE,
                    'max:'.PromoCode::MAX_FIXED_VALUE,
                ),
            ],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => $expiresAtRules,
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:'.PromoCode::MAX_USES],
        ], [
            'code.regex' => 'Код может содержать только латинские буквы, цифры, дефис и нижнее подчеркивание.',
            'value.max' => $request->input('type') === PromoCode::TYPE_PERCENT
                ? 'Процентная скидка не может быть больше '.PromoCode::MAX_PERCENT_VALUE.'%.'
                : 'Фиксированная скидка не может быть больше '.number_format(PromoCode::MAX_FIXED_VALUE, 0, ',', ' ').' руб.',
            'max_uses.max' => 'Лимит использований не может быть больше '.number_format(PromoCode::MAX_USES, 0, ',', ' ').'.',
        ]);

        $maxUses = $data['max_uses'] ?? null;

        if ($promoCode && $maxUses !== null && $maxUses < $promoCode->used_count) {
            throw ValidationException::withMessages([
                'max_uses' => "Лимит использований не может быть меньше уже выполненных применений: {$promoCode->used_count}.",
            ]);
        }

        return $data;
    }
}
