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
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();
                
            $table->string('from_status')->nullable();
            $table->string('to_status');
            
            $table->foreignId('changed_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
                
            $table->text('comment')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
