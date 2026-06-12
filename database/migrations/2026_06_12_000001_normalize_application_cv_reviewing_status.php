<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applications')
            ->where('status', 'new')
            ->update(['status' => 'cv_reviewing']);
    }

    public function down(): void
    {
        DB::table('applications')
            ->where('status', 'cv_reviewing')
            ->update(['status' => 'new']);
    }
};
