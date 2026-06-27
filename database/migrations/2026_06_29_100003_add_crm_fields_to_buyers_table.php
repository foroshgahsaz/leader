<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('pipeline_stage')->nullable()->after('status');
            $table->string('phone')->nullable()->after('website');
            $table->text('description')->nullable()->after('industry');
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn(['pipeline_stage', 'phone', 'description']);
        });
    }
};
