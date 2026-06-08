<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Vehicles Table
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('plate_number')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->default('petrol');
            $table->integer('current_odometer')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // 2. Maintenance Logs
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('service_type'); // oil change, tire rotation, etc.
            $table->integer('odometer');
            $table->decimal('cost', 10, 2);
            $table->date('service_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Fuel Logs
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->decimal('liters', 8, 2);
            $table->decimal('cost', 10, 2);
            $table->integer('odometer');
            $table->string('station_name')->nullable();
            $table->date('fill_date');
            $table->timestamps();
        });

        // 4. Trips History
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('origin');
            $table->string('destination');
            $table->decimal('distance_km', 8, 2);
            $table->dateTime('start_time')->nullable();
            $table->timestamps();
        });

        // 5. Smart Reminders
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['maintenance', 'insurance', 'license', 'other']);
            $table->date('due_date')->nullable();
            $table->integer('due_odometer')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // 6. Digital Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('type'); // License, Insurance, etc.
            $table->string('file_path');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('vehicles');
    }
};
