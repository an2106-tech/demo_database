<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->index(['status', 'updated_at'], 'applications_status_updated_at_index');
            $table->index(['status', 'applied_at'], 'applications_status_applied_at_index');
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->index(['scheduled_at', 'result'], 'interviews_scheduled_result_index');
            $table->index(['application_id', 'scheduled_at'], 'interviews_application_scheduled_index');
            $table->index(['interviewer_id', 'scheduled_at'], 'interviews_interviewer_scheduled_index');
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->index(['status', 'expires_at'], 'offers_status_expires_at_index');
            $table->index(['application_id', 'status'], 'offers_application_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->dropIndex('offers_status_expires_at_index');
            $table->dropIndex('offers_application_status_index');
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropIndex('interviews_scheduled_result_index');
            $table->dropIndex('interviews_application_scheduled_index');
            $table->dropIndex('interviews_interviewer_scheduled_index');
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->dropIndex('applications_status_updated_at_index');
            $table->dropIndex('applications_status_applied_at_index');
        });
    }
};
