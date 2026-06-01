<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('airline_id')->constrained()->cascadeOnDelete();
            $table->string('flight_number', 16)->unique();
            $table->string('origin');
            $table->string('destination');
            $table->dateTime('departure_at');
            $table->dateTime('arrival_at');
            $table->unsignedInteger('seats_total');
            $table->unsignedInteger('seats_available');
            $table->decimal('base_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
