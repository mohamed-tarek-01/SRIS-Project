<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('engine_cc')->nullable()->after('fuel_type');          // e.g. 1600
            $table->decimal('fuel_efficiency', 5, 2)->nullable()->after('engine_cc'); // km/L
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['engine_cc', 'fuel_efficiency']);
        });
    }
};
