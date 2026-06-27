<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_buyers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider_key')->unique();
            $table->string('legal_name');
            $table->string('display_name');
            $table->char('country_code', 2);
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_type')->nullable();
            $table->string('employee_range')->nullable();
            $table->json('firmographics')->nullable();
            $table->json('import_profile')->nullable();
            $table->unsignedTinyInteger('import_activity_level')->default(0);
            $table->timestamp('data_freshness_at')->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('industry');
            $table->index('company_type');
            $table->index('display_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_buyers');
    }
};
