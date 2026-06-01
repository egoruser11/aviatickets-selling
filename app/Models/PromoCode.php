<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    public const MAX_FIXED_VALUE = 100000;

    public const MAX_PERCENT_VALUE = 100;

    public const MAX_USES = 10000;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_PERCENT => 'Процент',
        self::TYPE_FIXED => 'Сумма',
    ];

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'is_active',
        'starts_at',
        'expires_at',
        'max_uses',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_uses === null || $this->used_count < $this->max_uses;
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === self::TYPE_PERCENT
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($subtotal, max(0, $discount)), 2);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
