<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_redemption_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Propriétaire des points convertis');
            $table->integer('points_redeemed');
            $table->unsignedInteger('conversion_rate_applied')
                ->comment('Ex. 100 = 100 pts pour 1 unité');
            $table->decimal('amount_usd', 10, 2);
            $table->decimal('amount_cdf', 15, 2);
            $table->decimal('daily_exchange_rate', 10, 4);
            $table->decimal('wallet_balance_before', 15, 2);
            $table->decimal('wallet_balance_after', 15, 2);
            $table->string('currency_used', 16)->default('USD');
            $table->foreignId('reference_loyalty_id')
                ->constrained('loyalty_history')
                ->restrictOnDelete()
                ->comment('Mouvement loyalty_history associé');
            $table->foreignId('created_by')
                ->constrained('users')
                ->comment('Utilisateur ayant déclenché la conversion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemption_history');
    }
};
