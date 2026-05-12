<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_preferences', function (Blueprint $table) {
            $table->text('pref_description')->nullable()->after('pref_name')
                ->comment('Texte d’aide affiché dans le tableau des réglages (sans la clé technique)');
        });
    }

    public function down(): void
    {
        Schema::table('app_preferences', function (Blueprint $table) {
            $table->dropColumn('pref_description');
        });
    }
};
