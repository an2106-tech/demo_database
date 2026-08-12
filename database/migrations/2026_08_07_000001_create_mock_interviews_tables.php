<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('recruitment_jobs')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->integer('current_step')->default(1); // 1..5
            $table->integer('total_score')->nullable(); // 0..100
            $table->json('summary_feedback')->nullable(); // pros, cons, recommendation, overall_summary
            $table->timestamps();
        });

        Schema::create('mock_interview_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_interview_id')->constrained('mock_interviews')->cascadeOnDelete();
            $table->integer('question_number'); // 1..5
            $table->text('question_text');
            $table->text('answer_text')->nullable();
            $table->integer('score')->nullable(); // 0..10
            $table->text('feedback')->nullable();
            $table->text('suggested_answer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_interview_messages');
        Schema::dropIfExists('mock_interviews');
    }
};
