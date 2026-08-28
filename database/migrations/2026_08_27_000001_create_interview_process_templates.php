<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_process_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('interview_process_template_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_process_template_id')
                ->index('interview_process_round_template_idx');
            $table->unsignedTinyInteger('round_number');
            $table->string('name', 150);
            $table->text('objective')->nullable();
            $table->foreignId('scorecard_template_id')
                ->nullable()
                ->index('interview_process_round_scorecard_idx');
            $table->json('evaluator_roles')->nullable();
            $table->timestamps();

            $table->unique(
                ['interview_process_template_id', 'round_number'],
                'interview_process_round_unique'
            );
            $table->foreign(
                'interview_process_template_id',
                'interview_process_round_template_fk'
            )
                ->references('id')
                ->on('interview_process_templates')
                ->cascadeOnDelete();
            $table->foreign('scorecard_template_id', 'interview_process_round_scorecard_fk')
                ->references('id')
                ->on('scorecard_templates')
                ->nullOnDelete();
        });

        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->foreignId('interview_process_template_id')
                ->nullable()
                ->after('department_id')
                ->constrained('interview_process_templates')
                ->nullOnDelete();
            $table->json('interview_process_snapshot')
                ->nullable()
                ->after('interview_process_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interview_process_template_id');
            $table->dropColumn('interview_process_snapshot');
        });

        Schema::dropIfExists('interview_process_template_rounds');
        Schema::dropIfExists('interview_process_templates');
    }
};
