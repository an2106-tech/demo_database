<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('candidates')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'user_id')) {
                return;
            }

            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('candidates')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            if (! Schema::hasColumn('candidates', 'user_id')) {
                return;
            }

            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

