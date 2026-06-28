<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('job_title')->nullable()->after('last_name');
            $table->string('avatar_path')->nullable()->after('job_title');
            $table->string('timezone')->nullable()->after('avatar_path');
            $table->string('locale', 10)->default('fa')->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('locale');
            $table->foreignUuid('current_organization_id')->nullable()->after('last_login_at')
                ->constrained('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_organization_id']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'job_title',
                'avatar_path',
                'timezone',
                'locale',
                'last_login_at',
                'current_organization_id',
            ]);
        });
    }
};
