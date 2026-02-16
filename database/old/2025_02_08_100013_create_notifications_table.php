<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('status_id');
            $table->unsignedInteger('object_id')->comment("ID de l'objet de la notification, ex. payment, rate, etc..");
            $table->string('object_name', 200)->comment("Nom de la table concerné par la notification pour lié avec l'objet_id");
            $table->unsignedInteger('notification_from')->nullable()->comment('If is a systme notification the field will be null');
            $table->unsignedInteger('notification_to');
            $table->unsignedInteger('viewed')->default(0)->comment('0=> Non, 1=>Oui');
            $table->string('message', 100)->comment('Short message to describe the notification');
            $table->text('metadata')->nullable()->comment('Un Json {} contenant toutes les informations necessaires pour la notification, ex. ride : {}, payment: {} etc...');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
