<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->nullable()->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('radius_km', 8, 2)->nullable()->comment('Rayon en km pour les zones circulaires');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Relation country gérée au niveau Eloquent (contrainte FK optionnelle)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
