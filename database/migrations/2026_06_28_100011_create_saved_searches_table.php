<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->json('criteria');
            $table->unsignedInteger('result_count_last')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
