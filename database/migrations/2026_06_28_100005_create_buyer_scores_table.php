<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('global_buyer_id')->nullable()->constrained('global_buyers')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('score');
            $table->string('score_band');
            $table->json('factors');
            $table->text('explanation')->nullable();
            $table->string('model_version')->default('rules-v1');
            $table->boolean('is_current')->default(true);
            $table->timestamp('scored_at');
            $table->timestamps();

            $table->index(['organization_id', 'score']);
            $table->index(['buyer_id', 'is_current']);
            $table->index(['global_buyer_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_scores');
    }
};
