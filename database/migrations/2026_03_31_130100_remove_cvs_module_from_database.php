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
        if (Schema::hasTable('applications')) {
            Schema::table('applications', function (Blueprint $table) {
                if (Schema::hasColumn('applications', 'cv_id')) {
                    $table->dropConstrainedForeignId('cv_id');
                }

                $dropColumns = [];
                if (Schema::hasColumn('applications', 'cv_title_snapshot')) {
                    $dropColumns[] = 'cv_title_snapshot';
                }
                if (Schema::hasColumn('applications', 'cv_file_snapshot')) {
                    $dropColumns[] = 'cv_file_snapshot';
                }

                if ($dropColumns !== []) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        Schema::dropIfExists('cvs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'cv_id')) {
                $table->foreignId('cv_id')
                    ->nullable()
                    ->after('candidate_id')
                    ->constrained('cvs')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('applications', 'cv_title_snapshot')) {
                $table->string('cv_title_snapshot')->nullable()->after('cv_path');
            }

            if (! Schema::hasColumn('applications', 'cv_file_snapshot')) {
                $table->string('cv_file_snapshot')->nullable()->after('cv_title_snapshot');
            }
        });
    }
};
