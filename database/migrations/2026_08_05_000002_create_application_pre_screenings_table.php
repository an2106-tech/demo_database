<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_pre_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_channel', 20);
            $table->dateTime('contacted_at');
            $table->string('outcome', 20);
            $table->dateTime('follow_up_at')->nullable();
            $table->text('note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
            $table->index(['outcome', 'follow_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_pre_screenings');
    }
};
