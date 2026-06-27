<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_list_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('lead_list_id')->constrained('lead_lists')->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lead_list_id', 'buyer_id']);
            $table->index(['buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_list_items');
    }
};
