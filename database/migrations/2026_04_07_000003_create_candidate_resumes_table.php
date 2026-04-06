<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_resumes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();

            $table->string('profile_title')->nullable();

            $table->json('personal_info')->nullable();
            $table->text('career_objective')->nullable();
            $table->json('desired_job')->nullable();

            $table->json('experiences')->nullable();
            $table->json('educations')->nullable();
            $table->json('certifications')->nullable();
            $table->json('languages')->nullable();
            $table->json('skills')->nullable();
            $table->json('achievements')->nullable();
            $table->json('activities')->nullable();
            $table->json('references')->nullable();
            $table->json('extra')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('candidate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_resumes');
    }
};

