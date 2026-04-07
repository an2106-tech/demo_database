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
        Schema::create('workplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['office', 'meeting_room', 'interview_room', 'remote', 'other'])->default('office');
            $table->string('floor', 20)->nullable();
            $table->string('room', 50)->nullable();
            $table->smallInteger('capacity')->unsigned()->nullable();
            $table->text('directions')->nullable();
            $table->string('map_url', 1000)->nullable();
            $table->boolean('is_interview_room')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplaces');
    }
};
