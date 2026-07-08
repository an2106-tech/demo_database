<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('cv_extraction_id')->nullable()->constrained('cv_extractions')->nullOnDelete();
            $table->string('analysis_type', 50)->default('screening');
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('recommendation', 30)->nullable();
            $table->text('summary')->nullable();
            $table->json('strengths')->nullable();
            $table->json('gaps')->nullable();
            $table->text('suggested_note')->nullable();
            $table->json('result_json')->nullable();
            $table->longText('raw_response')->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_from', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'analysis_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_ai_analyses');
    }
};
