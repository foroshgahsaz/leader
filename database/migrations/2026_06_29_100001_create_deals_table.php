<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->char('currency_code', 3)->default('USD');
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('lost_notes')->nullable();
            $table->timestamp('last_stage_change_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'buyer_id']);
            $table->index(['organization_id', 'stage_id']);
            $table->index(['organization_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
