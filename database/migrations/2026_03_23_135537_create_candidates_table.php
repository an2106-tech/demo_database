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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);

            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 50)->nullable()->index();

            $table->unsignedTinyInteger('experience_years')->nullable();

            $table->unsignedTinyInteger('match_score')->nullable();

            $table->json('match_reasons')->nullable();

            $table->boolean('blacklist')->default(0);

            $table->text('blacklist_reason')->nullable();

            $table->timestamp('blacklisted_at')->nullable();

            $table->foreignId('blacklisted_by')
                ->nullable()
                ->constrained('users');

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
