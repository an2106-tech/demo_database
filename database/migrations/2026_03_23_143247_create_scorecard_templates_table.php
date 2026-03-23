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
        Schema::create('scorecard_templates', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->json('criteria'); 
            $table->boolean('is_default')->default(0); 
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorecard_templates');
    }
};
