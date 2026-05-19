<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (! Schema::hasTable('media')) {
      Schema::create('media', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->unsignedBigInteger('vehicle_id')->nullable()->index();
        $table->string('name');
        $table->string('path');
        $table->enum('type', ['image', 'video']);
        $table->unsignedBigInteger('size')->nullable();
        $table->string('mime_type', 100)->nullable();
        $table->string('disk', 50)->default('s3_media');
        $table->timestamps();
      });

      return;
    }

    Schema::table('media', function (Blueprint $table): void {
      if (! Schema::hasColumn('media', 'user_id')) {
        $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
      }
      if (! Schema::hasColumn('media', 'vehicle_id')) {
        $table->unsignedBigInteger('vehicle_id')->nullable()->index()->after('user_id');
      }
    });
  }

  public function down(): void
  {
    if (! Schema::hasTable('media')) {
      return;
    }

    Schema::table('media', function (Blueprint $table): void {
      if (Schema::hasColumn('media', 'vehicle_id')) {
        $table->dropColumn('vehicle_id');
      }
      if (Schema::hasColumn('media', 'user_id')) {
        $table->dropColumn('user_id');
      }
    });
  }
};
