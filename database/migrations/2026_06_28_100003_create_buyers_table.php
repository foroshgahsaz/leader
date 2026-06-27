<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('global_buyer_id')->nullable()->constrained('global_buyers')->nullOnDelete();
            $table->string('provider_key')->nullable();
            $table->string('name');
            $table->char('country_code', 2);
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_type')->nullable();
            $table->string('employee_range')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('search');
            $table->uuid('source_search_id')->nullable();
            $table->string('status')->default('saved');
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_dnc')->default(false);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'global_buyer_id']);
            $table->unique(['organization_id', 'provider_key']);
            $table->index(['organization_id', 'owner_id']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'country_code']);
            $table->index(['organization_id', 'is_favorite']);
            $table->index(['organization_id', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
