<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_pre_screenings', function (Blueprint $table): void {
            $table->string('rejection_reason_code', 50)->nullable()->after('rejection_reason');
            $table->dateTime('follow_up_reminded_at')->nullable()->after('follow_up_at');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY rejected_stage ENUM('screening', 'pre_screening', 'interview', 'offer') NULL");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY rejected_stage ENUM('screening', 'interview', 'offer') NULL");
        }

        Schema::table('application_pre_screenings', function (Blueprint $table): void {
            $table->dropColumn(['rejection_reason_code', 'follow_up_reminded_at']);
        });
    }
};
