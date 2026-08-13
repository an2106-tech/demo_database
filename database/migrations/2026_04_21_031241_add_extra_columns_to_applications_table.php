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
            $table->foreignId('assigned_hr_id')->nullable()->after('referral_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('assigned_hr_id')->constrained('branches')->nullOnDelete();
            
            $table->enum('rejected_stage', ['screening', 'pre_screening', 'interview', 'offer'])->nullable()->after('status');
            
            $table->boolean('is_viewed')->default(false)->after('rejected_stage');
            $table->timestamp('viewed_at')->nullable()->after('is_viewed');
            
            $table->foreignId('applied_by')->nullable()->after('viewed_at')->constrained('users')->nullOnDelete();
            $table->boolean('is_duplicate')->default(false)->after('applied_by');

            // Cập nhật lại enum status nếu DB hỗ trợ, hoặc để code handle logic
            // Lưu ý: MySQL require change migration
            $table->string('status')->default('cv_reviewing')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['assigned_hr_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['applied_by']);
            $table->dropColumn([
                'assigned_hr_id',
                'branch_id',
                'rejected_stage',
                'is_viewed',
                'viewed_at',
                'applied_by',
                'is_duplicate'
            ]);
            $table->enum('status', [
                'new',
                'screening',
                'interview',
                'offer',
                'rejected',
                'hired'
            ])->default('new')->change();
        });
    }
};
