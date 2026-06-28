<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('locale', 'en')
            ->update(['locale' => 'fa']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('locale', 'fa')
            ->update(['locale' => 'en']);
    }
};
