<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidate_job_submissions', function (Blueprint $table) {
            $table->integer('ai_matching_score')->nullable()->after('cv_text_snapshot');
            $table->json('ai_analysis')->nullable()->after('ai_matching_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_job_submissions', function (Blueprint $table) {
            $table->dropColumn(['ai_matching_score', 'ai_analysis']);
        });
    }
};
