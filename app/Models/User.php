<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
        'blocked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    public function promoCodeUsages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'blocked_at' => 'datetime',
        ];
    }
}
