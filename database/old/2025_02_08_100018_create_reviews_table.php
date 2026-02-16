<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('status_id');
            $table->timestamps();
            $table->unsignedInteger('reviewer_id')->nullable();
            $table->unsignedInteger('reviewee_id')->nullable();
            $table->unsignedInteger('ride_id')->nullable();
            $table->unsignedInteger('rating')->nullable();
            $table->text('comment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
