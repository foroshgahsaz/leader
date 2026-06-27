<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('global_buyer_id')->nullable()->constrained('global_buyers')->cascadeOnDelete();
            $table->text('summary');
            $table->json('key_facts')->nullable();
            $table->string('suggested_angle')->nullable();
            $table->string('confidence')->default('medium');
            $table->json('data_gaps')->nullable();
            $table->string('model_version')->default('template-v1');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['buyer_id']);
            $table->index(['global_buyer_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_summaries');
    }
};
