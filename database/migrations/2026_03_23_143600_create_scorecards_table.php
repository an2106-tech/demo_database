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
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('interview_id')->nullable()->constrained('interviews')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('scorecard_templates')->nullOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->json('criteria')->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('conclusion', ['pass', 'fail', 'hold'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorecards');
    }
};
