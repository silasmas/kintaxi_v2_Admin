<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kyc_verifications')) {
            return;
        }

        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('job_id', 191);
            $table->string('product_type', 100)->default('document_verification');
            $table->string('document_type', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->longText('smile_result_json')->nullable();
            $table->longText('callback_payload_json')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->unique('job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
