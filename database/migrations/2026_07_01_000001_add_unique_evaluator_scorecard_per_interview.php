<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('scorecards')
            ->select('interview_id', 'evaluator_id', DB::raw('MAX(id) as keep_id'))
            ->whereNotNull('interview_id')
            ->groupBy('interview_id', 'evaluator_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('interview_id')
            ->chunk(100, function ($duplicates): void {
                foreach ($duplicates as $duplicate) {
                    DB::table('scorecards')
                        ->where('interview_id', $duplicate->interview_id)
                        ->where('evaluator_id', $duplicate->evaluator_id)
                        ->where('id', '<>', $duplicate->keep_id)
                        ->delete();
                }
            });

        Schema::table('scorecards', function (Blueprint $table) {
            $table->unique(['interview_id', 'evaluator_id'], 'scorecards_interview_evaluator_unique');
        });
    }

    public function down(): void
    {
        Schema::table('scorecards', function (Blueprint $table) {
            $table->dropUnique('scorecards_interview_evaluator_unique');
        });
    }
};
