<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->char('country_code', 2)->default('US');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('active');
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
