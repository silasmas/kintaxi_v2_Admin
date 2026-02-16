<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by')->comment("L'initiateur de transaction");
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedInteger('user_id')->comment('Le proprietaire de la transaction');
            $table->unsignedInteger('ride_id')->nullable()->comment('Null si la transaction concerne un depot ou un retrait');
            $table->enum('type', ['deposit', 'withdrawal', 'ride_payment', 'commission']);
            $table->decimal('amount', 10, 2)->comment('Amount en CDF');
            $table->decimal('wallet_balance_before', 10, 2)->nullable();
            $table->decimal('wallet_balance_after', 10, 2)->nullable();
            $table->unsignedInteger('payment_id')->nullable()->unique()->comment('A remplir si la transaction est lié à un depot/retrait/ ou paiement de course via mobile money');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
