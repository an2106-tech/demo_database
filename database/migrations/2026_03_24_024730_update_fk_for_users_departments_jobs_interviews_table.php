<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('code');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });

        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');

            $table->unsignedBigInteger('branch_id')->after('slug');
            $table->unsignedBigInteger('workplace_id')->nullable()->after('branch_id');

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('workplace_id')
                ->references('id')
                ->on('workplaces')
                ->nullOnDelete();
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');

            $table->unsignedBigInteger('workplace_id')->nullable()->after('interviewer_id');

            $table->foreign('workplace_id')
                ->references('id')
                ->on('workplaces')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['workplace_id']);
            $table->dropColumn(['branch_id', 'workplace_id']);
            $table->unsignedBigInteger('location_id')->nullable()->after('slug'); // khôi phục cột cũ
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['workplace_id']);
            $table->dropColumn('workplace_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('interviewer_id'); // khôi phục
        });
    }
};
