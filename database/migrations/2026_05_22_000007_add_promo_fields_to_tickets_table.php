<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('promo_code_id')->nullable()->after('flight_id')->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
