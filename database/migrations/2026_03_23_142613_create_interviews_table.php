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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();

            $table->foreignId('interviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->tinyInteger('round_number')->unsigned()->default(1);
            $table->string('round_name', 100)->nullable();
            $table->dateTime('scheduled_at');
            $table->smallInteger('duration_minutes')->unsigned()->default(60);
            $table->enum('type', ['online', 'offline'])->default('online');
            $table->string('meeting_link', 500)->nullable();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->timestamp('invite_sent_at')->nullable();
            $table->timestamp('invite_confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
