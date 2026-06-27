<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->foreignUuid('buyer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['import_batch_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
    }
};
