<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->comment('User/Admin who created the item');
            $table->text('status_name');
            $table->text('status_description')->nullable();
            $table->string('icon', 45)->nullable();
            $table->string('color', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};
