<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignUuid('buyer_id')->nullable()->after('entity_id')->constrained()->nullOnDelete();
            $table->foreignUuid('deal_id')->nullable()->after('buyer_id')->constrained()->nullOnDelete();

            $table->index(['buyer_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deal_id');
            $table->dropConstrainedForeignId('buyer_id');
        });
    }
};
