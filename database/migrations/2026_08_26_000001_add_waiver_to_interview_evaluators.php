<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_evaluators', function (Blueprint $table): void {
            $table->timestamp('waived_at')->nullable()->after('submitted_at')->index();
            $table->foreignId('waived_by_user_id')
                ->nullable()
                ->after('waived_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('waiver_reason', 500)->nullable()->after('waived_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('interview_evaluators', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('waived_by_user_id');
            $table->dropColumn(['waived_at', 'waiver_reason']);
        });
    }
};
