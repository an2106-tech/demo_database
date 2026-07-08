<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_extractions', function (Blueprint $table) {
            $table->id();
            $table->string('cv_hash', 64)->unique();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();

            $table->index('file_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_extractions');
    }
};
