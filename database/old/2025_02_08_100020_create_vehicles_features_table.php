<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles_features', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('vehicle_id');
            $table->unsignedTinyInteger('is_clean')->default(0);
            $table->unsignedTinyInteger('has_helmet')->default(0);
            $table->unsignedTinyInteger('has_airbags')->default(0);
            $table->unsignedTinyInteger('has_seat_belt')->default(0);
            $table->unsignedTinyInteger('has_ergonomic_seat')->default(0);
            $table->unsignedTinyInteger('has_air_conditioning')->default(0);
            $table->unsignedTinyInteger('has_soundproofing')->default(0);
            $table->unsignedTinyInteger('has_sufficient_space')->default(0);
            $table->unsignedTinyInteger('has_quality_equipment')->default(0);
            $table->unsignedTinyInteger('has_on_board_technologies')->default(0);
            $table->unsignedTinyInteger('has_interior_lighting')->default(0);
            $table->unsignedTinyInteger('has_practical_accessories')->default(0);
            $table->unsignedTinyInteger('has_driving_assist_system')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles_features');
    }
};
