<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignUuid('deal_id')->nullable()->after('buyer_id')->constrained()->nullOnDelete();
            $table->timestamp('snoozed_until')->nullable()->after('due_at');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deal_id');
            $table->dropColumn('snoozed_until');
        });
    }
};
