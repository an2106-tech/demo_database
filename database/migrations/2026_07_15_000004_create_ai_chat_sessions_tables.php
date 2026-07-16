<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('audience', 30);
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'audience', 'is_active']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->json('sources')->nullable();
            $table->json('suggestions')->nullable();
            $table->string('status', 30)->default('completed');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('intent')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->string('feedback', 30)->nullable();
            $table->timestamps();

            $table->index(['ai_chat_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
