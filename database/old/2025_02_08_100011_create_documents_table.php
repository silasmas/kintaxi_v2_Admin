<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('status_id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Le proprio du document');
            $table->unsignedInteger('verified')->default(0)->comment('0=>non verfié, 1=>verifié');
            $table->datetime('verified_at')->nullable()->comment("Date de verification par l'admin");
            $table->unsignedInteger('verified_by')->nullable()->comment("Admin qui a verifier le document");
            $table->enum('type', ['id_card', 'driving_license', 'vehicle_registration', 'vehicle_insurance']);
            $table->string('file_url', 1000);
            $table->unsignedInteger('vehicle_id')->nullable()->comment('NUll si le document ne concerne pas le vehicule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
