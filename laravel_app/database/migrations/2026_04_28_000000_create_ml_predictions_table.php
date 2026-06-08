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
        Schema::create('ml_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('model_type'); // 'plate', 'cracks', 'accident', etc.
            $table->string('image_path')->nullable(); // Path to uploaded image
            $table->longText('prediction_result'); // JSON result from ML model
            $table->float('confidence_score')->nullable(); // Confidence percentage
            $table->integer('execution_time_ms')->nullable(); // How long the prediction took
            $table->string('input_type')->default('image'); // 'image' or 'video'
            $table->integer('station_id')->nullable(); // Associated station if applicable
            $table->string('location_text')->nullable(); // User-provided location info
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('model_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_predictions');
    }
};
