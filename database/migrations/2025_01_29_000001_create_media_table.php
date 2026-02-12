<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path'); // Chemin S3 (ex: images/2025/01/29/uuid.jpg)
            $table->enum('type', ['image', 'video']);
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('disk', 50)->default('s3_media');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
