<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('seat_number', 8);
            $table->string('status', 16);
            $table->decimal('price', 10, 2);
            $table->dateTime('purchased_at');
            $table->timestamps();

            $table->unique(['flight_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
