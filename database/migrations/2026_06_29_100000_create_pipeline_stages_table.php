<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);
            $table->string('color')->default('gray');
            $table->timestamps();

            $table->unique(['organization_id', 'key']);
            $table->index(['organization_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
