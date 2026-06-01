<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_code_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'promo_code_id']);
        });

        DB::table('tickets')
            ->select(['user_id', 'promo_code_id'])
            ->whereNotNull('user_id')
            ->whereNotNull('promo_code_id')
            ->distinct()
            ->orderBy('user_id')
            ->orderBy('promo_code_id')
            ->get()
            ->each(function ($ticket): void {
                DB::table('promo_code_usages')->insert([
                    'user_id' => $ticket->user_id,
                    'promo_code_id' => $ticket->promo_code_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_usages');
    }
};
