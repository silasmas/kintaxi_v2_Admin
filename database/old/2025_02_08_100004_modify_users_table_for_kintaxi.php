<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('id');
            $table->unsignedBigInteger('role_id')->nullable()->after('status_id');
            $table->string('firstname')->nullable()->after('role_id');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('surname')->nullable()->after('lastname');
            $table->string('username')->nullable()->after('surname');
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('gender', 45)->nullable()->after('phone');
            $table->date('birthdate')->nullable()->after('gender');
            $table->string('country_code', 4)->nullable()->comment('Code du pays (Exemple RDC => CD)')->after('birthdate');
            $table->string('city', 45)->nullable()->after('country_code');
            $table->text('address_1')->nullable()->after('city');
            $table->text('address_2')->nullable()->after('address_1');
            $table->string('p_o_box', 45)->nullable()->after('address_2');
            $table->unsignedBigInteger('belongs_to')->nullable()->after('password');
            $table->datetime('phone_verified_at')->nullable()->after('email_verified_at');
            $table->text('api_token')->nullable()->after('remember_token');
            $table->text('avatar_url')->nullable()->after('api_token');
            $table->string('session_socket_io', 512)->nullable()->after('avatar_url');
            $table->text('fcm_token')->nullable()->comment('Firebase token genere pour les push notifications')->after('session_socket_io');
            $table->float('rate')->nullable()->comment("Note total de l'utilisateur")->after('fcm_token');
            $table->integer('activation_otp')->nullable()->after('rate');
            $table->float('wallet_balance')->default(0)->comment('Solde en CDF')->after('activation_otp');
            $table->integer('loyalty_point')->default(0)->comment('Point de fedelité')->after('wallet_balance');
            $table->integer('current_vehicle_id')->nullable()->comment('Vehicule que le chauffeur conduit par defaut')->after('loyalty_point');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status_id', 'role_id', 'firstname', 'lastname', 'surname', 'username',
                'phone', 'gender', 'birthdate', 'country_code', 'city', 'address_1', 'address_2',
                'p_o_box', 'belongs_to', 'phone_verified_at', 'api_token', 'avatar_url',
                'session_socket_io', 'fcm_token', 'rate', 'activation_otp', 'wallet_balance',
                'loyalty_point', 'current_vehicle_id',
            ]);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
