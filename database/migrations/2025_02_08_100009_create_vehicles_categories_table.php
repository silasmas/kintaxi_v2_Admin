<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('status_id');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('category_name')->nullable();
            $table->string('category_description', 1000)->nullable();
            $table->string('image', 1000)->nullable()->comment('URL de la photo à utiliser comme icon');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles_categories');
    }
};
