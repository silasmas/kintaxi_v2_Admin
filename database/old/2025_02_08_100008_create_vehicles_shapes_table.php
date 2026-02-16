<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles_shapes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->text('shape_name')->nullable();
            $table->text('shape_description')->nullable();
            $table->string('photo', 1000)->nullable()->comment('Phot de la forme/dody des vehicules');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles_shapes');
    }
};
