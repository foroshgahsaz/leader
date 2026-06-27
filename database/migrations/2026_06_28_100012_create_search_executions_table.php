<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('saved_search_id')->nullable()->constrained()->nullOnDelete();
            $table->json('criteria');
            $table->unsignedInteger('result_count')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedSmallInteger('credit_consumed')->default(1);
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['organization_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_executions');
    }
};
