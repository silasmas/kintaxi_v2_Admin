<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by')->comment("L'initiateur de paiement");
            $table->unsignedInteger('updated_by');
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('reference', 45)->nullable();
            $table->string('provider_reference', 45)->nullable();
            $table->string('phone', 45)->nullable();
            $table->decimal('amount_customer', 9, 2)->nullable();
            $table->decimal('amount', 9, 2)->nullable();
            $table->string('currency', 45)->nullable();
            $table->string('channel', 45)->nullable();
            $table->unsignedInteger('gateway')->nullable()->comment('1=flexpay, etc... voir la table payment_gateways');
            $table->unsignedInteger('ride_id')->nullable()->comment("Si paiment d'une course");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
