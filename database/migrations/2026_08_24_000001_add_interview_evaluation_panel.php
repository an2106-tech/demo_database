<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_evaluators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['lead', 'member'])->default('member');
            $table->boolean('is_required')->default(true);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['interview_id', 'user_id'], 'interview_evaluators_unique');
            $table->index(['interview_id', 'is_required', 'submitted_at'], 'interview_evaluators_progress_index');
        });

        Schema::table('scorecards', function (Blueprint $table): void {
            $table->timestamp('submitted_at')->nullable()->after('conclusion')->index();
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->timestamp('finalized_at')->nullable()->after('actual_ended_at')->index();
            $table->foreignId('finalized_by_user_id')
                ->nullable()
                ->after('finalized_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('final_notes')->nullable()->after('finalized_by_user_id');
        });

        DB::table('interviews')
            ->whereNotNull('interviewer_id')
            ->orderBy('id')
            ->chunkById(200, function ($interviews): void {
                $now = now();
                $submittedScorecards = DB::table('scorecards')
                    ->whereIn('interview_id', $interviews->pluck('id'))
                    ->whereNotNull('conclusion')
                    ->get(['interview_id', 'evaluator_id', 'updated_at'])
                    ->keyBy(fn ($scorecard): string => $scorecard->interview_id.':'.$scorecard->evaluator_id);
                $rows = $interviews->map(fn ($interview): array => [
                    'interview_id' => $interview->id,
                    'user_id' => $interview->interviewer_id,
                    'role' => 'lead',
                    'is_required' => true,
                    'assigned_at' => $interview->created_at ?: $now,
                    'submitted_at' => $submittedScorecards
                        ->get($interview->id.':'.$interview->interviewer_id)
                        ?->updated_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('interview_evaluators')->insertOrIgnore($rows);
            });

        DB::table('scorecards')
            ->whereNotNull('conclusion')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('finalized_by_user_id');
            $table->dropColumn(['finalized_at', 'final_notes']);
        });

        Schema::table('scorecards', function (Blueprint $table): void {
            $table->dropColumn('submitted_at');
        });

        Schema::dropIfExists('interview_evaluators');
    }
};
