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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('recruitment_jobs')
                ->cascadeOnDelete();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();

            $table->string('cv_path');

            $table->enum('source', [
                'website',
                'facebook',
                'linkedin',
                'referral',
                'other'
            ])->default('website');

            $table->foreignId('referral_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->enum('status', [
                'new',
                'screening',
                'interview',
                'offer',
                'rejected',
                'hired'
            ])->default('new');

            $table->json('salary_expected')->nullable();

            $table->timestamp('applied_at')->useCurrent();

            $table->text('rejected_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['job_id', 'candidate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
