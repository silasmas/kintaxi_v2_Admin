<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('status_id');
            $table->string('method_name');
            $table->unsignedBigInteger('payment_gateway_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
