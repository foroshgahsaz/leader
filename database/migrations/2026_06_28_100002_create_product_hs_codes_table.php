<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_hs_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('hs_code', 12);
            $table->string('description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'hs_code']);
            $table->index(['organization_id', 'hs_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_hs_codes');
    }
};
