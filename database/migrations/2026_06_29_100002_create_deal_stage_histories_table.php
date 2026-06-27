<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_stage_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('from_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignUuid('to_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['deal_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_stage_histories');
    }
};
