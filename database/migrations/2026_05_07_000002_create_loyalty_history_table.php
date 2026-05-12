<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Utilisateur concerné');
            $table->integer('points_earned')->comment('Positif = gain, négatif = dépense');
            $table->integer('points_before_transaction');
            $table->integer('points_after_transaction');
            $table->string('transaction_type', 32)
                ->comment('ride, referral, bonus, redemption, other');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('ID lié (course, parrainage, etc.)');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_history');
    }
};
