<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_preferences', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Dernier utilisateur ayant modifié la préférence');
            $table->string('pref_key', 100)->unique();
            $table->string('pref_value', 1000)->nullable();
            $table->string('pref_name', 200);
            $table->string('pref_type', 32)
                ->comment('text, number, multiple_choice, radio');
            $table->string('pref_expected_value', 1000)->nullable()
                ->comment('Valeurs attendues (options séparées par virgule, ou valeur par défaut)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_preferences');
    }
};
