<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flight extends Model
{
    protected $fillable = [
        'airline_id',
        'flight_number',
        'origin',
        'destination',
        'departure_at',
        'arrival_at',
        'seats_total',
        'seats_available',
        'base_price',
    ];

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'arrival_at' => 'datetime',
            'base_price' => 'decimal:2',
        ];
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function seatNumbers(): array
    {
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        $seats = [];

        for ($index = 0; $index < $this->seats_total; $index++) {
            $row = intdiv($index, count($letters)) + 1;
            $seats[] = $row.$letters[$index % count($letters)];
        }

        return $seats;
    }
}
