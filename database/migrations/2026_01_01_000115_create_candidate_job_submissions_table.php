<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_job_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('recruitment_jobs')
                ->cascadeOnDelete();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();

            $table->enum('apply_method', ['profile', 'cv'])->default('profile');

            $table->json('profile_snapshot')->nullable();

            $table->string('cv_path')->nullable();
            $table->unsignedBigInteger('cv_attachment_id')->nullable();
            $table->longText('cv_text_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['job_id', 'candidate_id']);
            $table->index(['candidate_id', 'apply_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_job_submissions');
    }
};

