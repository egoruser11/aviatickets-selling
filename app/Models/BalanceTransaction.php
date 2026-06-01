<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceTransaction extends Model
{
    public const TYPE_TOP_UP = 'top_up';

    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_REFUND = 'refund';

    public const TYPE_ADMIN = 'admin';

    public const TYPES = [
        self::TYPE_TOP_UP => 'Пополнение',
        self::TYPE_PURCHASE => 'Покупка билета',
        self::TYPE_REFUND => 'Возврат',
        self::TYPE_ADMIN => 'Корректировка администратора',
    ];

    protected $fillable = [
        'user_id',
        'ticket_id',
        'type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
