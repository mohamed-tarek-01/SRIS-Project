<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('fuel_consumed', 8, 2)->nullable()->after('distance_km'); // litres
            $table->decimal('fuel_cost', 10, 2)->nullable()->after('fuel_consumed');  // EGP
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['fuel_consumed', 'fuel_cost']);
        });
    }
};
