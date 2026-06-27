<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('contact_id')->nullable()->constrained('buyer_contacts')->nullOnDelete();
            $table->foreignId('logged_by')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['buyer_id', 'occurred_at']);
            $table->index(['organization_id', 'activity_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
