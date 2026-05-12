<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schéma utilisateurs (Fusion des anciennes migrations create_users + modify_users Kintaxi).
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('surname')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('gender', 45)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('country_code', 4)->nullable()->comment('Code du pays (Exemple RDC => CD)');
            $table->string('city', 45)->nullable();
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->string('p_o_box', 45)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->dateTime('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('belongs_to')->nullable();
            $table->rememberToken();
            $table->text('api_token')->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('session_socket_io', 512)->nullable();
            $table->text('fcm_token')->nullable()->comment('Firebase token pour les notifications push');
            $table->float('rate')->nullable()->comment("Note globale de l'utilisateur");
            $table->integer('activation_otp')->nullable();
            $table->float('wallet_balance')->default(0)->comment('Solde en CDF');
            $table->integer('loyalty_point')->default(0)->comment('Points de fidélité');
            $table->integer('current_vehicle_id')->nullable()->comment('Véhicule par défaut du chauffeur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
