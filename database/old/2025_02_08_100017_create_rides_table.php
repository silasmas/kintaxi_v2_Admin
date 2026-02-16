<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('ride_status', ['requested', 'accepted', 'in_progress', 'completed', 'canceled'])->default('requested');
            $table->unsignedBigInteger('vehicle_category_id')->nullable()->comment('Catégegori du vehicule');
            $table->unsignedInteger('vehicle_id')->nullable();
            $table->unsignedInteger('passenger_id');
            $table->unsignedInteger('driver_id')->nullable();
            $table->decimal('distance', 9, 2)->comment('En km');
            $table->decimal('cost', 9, 2)->nullable();
            $table->float('estimated_cost')->nullable()->comment('Prix estimatif avant que la course commence');
            $table->enum('payment_method', ['cash', 'kintaxi-wallet', 'mobile-money', 'card']);
            $table->boolean('paid')->nullable()->comment('1=>OUI,0=>Non');
            $table->unsignedInteger('payment_id')->nullable()->comment('ID du paiment si le piement method est card ou mobile money');
            $table->float('commission')->default(15)->comment('Commission appliqué valeur en pourcentage, par defaut 15%');
            $table->text('start_location')->nullable();
            $table->text('end_location')->nullable();
            $table->text('pickup_location')->nullable();
            $table->text('pickup_data')->nullable()->comment('Contiens les données de la distance et la durée entre la localisation du chauffeur et du client (JSON)');
            $table->text('driver_location')->nullable()->comment('La localisation du chauffeur quand il accpte la course (JSON)');
            $table->decimal('estimated_duration', 5, 2)->nullable()->comment('En minutes');
            $table->decimal('actual_duration', 5, 2)->nullable()->comment('En minutes');
            $table->decimal('waiting_time', 5, 2)->default(0.00)->comment("Le temps d'attente que le chauffeur fait avant de prendre le client, En minute");
            $table->boolean('is_scheduled')->default(false)->comment('Indique si la course est planifiée ou non');
            $table->datetime('scheduled_time')->nullable()->comment('Date et Heure planifiée pour la course');
            $table->unsignedInteger('cancellation_reason')->nullable();
            $table->enum('canceled_by', ['passenger', 'driver'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
