<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applications')
            ->where('status', 'offered')
            ->update(['status' => 'offer']);

        DB::table('application_status_histories')
            ->where('from_status', 'offered')
            ->update(['from_status' => 'offer']);

        DB::table('application_status_histories')
            ->where('to_status', 'offered')
            ->update(['to_status' => 'offer']);
    }

    public function down(): void
    {
        // Irreversible data normalization: "offered" is not valid for StatusApplicationEnum.
    }
};
