<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id', 'changed_at']);
            $table->index(['organization_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_histories');
    }
};
