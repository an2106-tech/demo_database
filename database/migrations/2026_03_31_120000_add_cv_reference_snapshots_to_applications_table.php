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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('cv_id')
                ->nullable()
                ->after('candidate_id')
                ->constrained('cvs')
                ->nullOnDelete();

            $table->string('cv_title_snapshot')
                ->nullable()
                ->after('cv_path');

            $table->string('cv_file_snapshot')
                ->nullable()
                ->after('cv_title_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cv_id');
            $table->dropColumn(['cv_title_snapshot', 'cv_file_snapshot']);
        });
    }
};
