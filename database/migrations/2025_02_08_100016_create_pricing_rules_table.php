<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->enum('rule_type', ['base_fare', 'distance', 'time', 'waiting_time', 'traffic']);
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->unsignedInteger('vehicle_category')->default(1);
            $table->decimal('surge_multiplier', 3, 2)->default(1.00)->comment("Le surge_multiplier (multiplicateur de surcharge) est utilisé pour augmenter les tarifs lorsque la demande de taxis est élevée ou lorsque les conditions de trafic ou d'événements spéciaux justifient une majoration.");
            $table->enum('unit', ['km', 'min', 'fixed', 'percentage'])->comment("unit permet de préciser l'unité de mesure pour chaque règle tarifaire : km : Pour les règles tarifaires qui dépendent de la distance (par kilomètre). min : Pour les règles qui dépendent du temps (par minute). fixed : Pour les tarifs fixes (comme le prix de base base_fare). percentage : Si tu veux appliquer une règle basée sur un pourcentage (ex. : une réduction ou un supplément basé sur un pourcentage du coût total).");
            $table->unsignedInteger('zone_id')->nullable()->comment('A ajouster plutard, pour la gestion de tarif par zone geographique');
            $table->timestamp('valid_from')->nullable()->useCurrent();
            $table->timestamp('valid_to')->nullable();
            $table->unsignedInteger('is_default')->default(0)->comment("Si c'est 1, donc c'est le prix defini par defaut pour la categorie et qui utilisé s'il y a pas des prix qui rempli le critere");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
