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
        Schema::table('offers', function (Blueprint $table) {
            // Change enum to include approval workflow statuses.
            $table->enum('status', ['draft', 'awaiting_approval', 'pending', 'accepted', 'declined', 'rejected', 'expired'])
                ->change();
            
            // Add fields for tracking approval workflow
            $table->timestamp('approval_requested_at')->nullable()->after('sent_at');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('approval_requested_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Revert enum back to original
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])
                ->change();
            
            // Drop new columns
            $table->dropColumn(['approval_requested_at', 'approval_notes']);
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn('approved_at');
        });
    }
};
