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
        Schema::create('recruitment_jobs', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->index();

            $table->mediumText('description');

            $table->enum('status', ['draft', 'pending', 'published', 'closed', 'archived', 'expired'])
                ->default('draft');

            $table->json('salary_range')->nullable();

            $table->date('deadline')->nullable();

            $table->unsignedTinyInteger('positions_count')->default(1);

            $table->string('public_url')->unique()->nullable();

            $table->string('thumbnail')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('workplace_id')
                ->nullable()
                ->constrained('workplaces')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_jobs');
    }
};
