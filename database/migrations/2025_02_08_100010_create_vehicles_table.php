<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('status_id')->comment("Apres verification par l'admin, passer à 1 pour que le vehicule soit autorisé à etre utilisé");
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Le proprio du vehicule');
            $table->string('model')->nullable();
            $table->string('mark')->nullable();
            $table->string('color', 45)->nullable();
            $table->string('registration_number')->nullable()->comment("Plaque d'immatriculation");
            $table->string('vin_number')->nullable()->comment('Numero de chassi');
            $table->integer('manufacture_year')->nullable();
            $table->string('fuel_type')->nullable();
            $table->decimal('cylinder_capacity', 9, 2)->nullable();
            $table->decimal('engine_power', 9, 2)->nullable();
            $table->unsignedBigInteger('shape_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedInteger('nb_places')->comment('Nombre de place sans le siege du chauffeur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
