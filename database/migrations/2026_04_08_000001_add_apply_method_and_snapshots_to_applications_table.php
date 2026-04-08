<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'apply_method')) {
                $table->enum('apply_method', ['profile', 'cv'])
                    ->default('profile')
                    ->after('cv_path');
            }

            if (! Schema::hasColumn('applications', 'profile_snapshot')) {
                $table->json('profile_snapshot')->nullable()->after('apply_method');
            }

            if (! Schema::hasColumn('applications', 'cv_attachment_id')) {
                $table->unsignedBigInteger('cv_attachment_id')->nullable()->after('profile_snapshot');
                $table->index('cv_attachment_id');
            }

            if (! Schema::hasColumn('applications', 'cv_text_snapshot')) {
                $table->longText('cv_text_snapshot')->nullable()->after('cv_attachment_id');
            }
        });

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // Make cv_path nullable so profile-only applications do not require an uploaded file.
            DB::statement('ALTER TABLE applications MODIFY cv_path VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // Revert to NOT NULL (if you have NULL rows, migrate data first).
            DB::statement("UPDATE applications SET cv_path = '' WHERE cv_path IS NULL");
            DB::statement('ALTER TABLE applications MODIFY cv_path VARCHAR(255) NOT NULL');
        }

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'cv_text_snapshot')) {
                $table->dropColumn('cv_text_snapshot');
            }
            if (Schema::hasColumn('applications', 'cv_attachment_id')) {
                $table->dropIndex(['cv_attachment_id']);
                $table->dropColumn('cv_attachment_id');
            }
            if (Schema::hasColumn('applications', 'profile_snapshot')) {
                $table->dropColumn('profile_snapshot');
            }
            if (Schema::hasColumn('applications', 'apply_method')) {
                $table->dropColumn('apply_method');
            }
        });
    }
};

