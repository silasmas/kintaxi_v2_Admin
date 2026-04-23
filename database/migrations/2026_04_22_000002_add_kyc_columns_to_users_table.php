<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'kyc_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('kyc_verified')->default(false);
            });
        }

        if (! Schema::hasColumn('users', 'kyc_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('kyc_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'kyc_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kyc_verified_at');
            });
        }

        if (Schema::hasColumn('users', 'kyc_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kyc_verified');
            });
        }
    }
};
