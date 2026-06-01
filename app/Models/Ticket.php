<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    public const STATUSES = [
        'booked' => 'Забронирован',
        'paid' => 'Оплачен',
        'cancelled' => 'Отменен',
    ];

    protected $fillable = [
        'user_id',
        'flight_id',
        'promo_code_id',
        'passenger_name',
        'passenger_email',
        'seat_number',
        'status',
        'price',
        'discount_amount',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
