<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attachments')) {
            return;
        }

        Schema::table('attachments', function (Blueprint $table) {
            $table->index(['attachable_type', 'attachable_id'], 'attachments_attachable_index');
            $table->index('type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attachments')) {
            return;
        }

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('attachments_attachable_index');
            $table->dropIndex(['type']);
        });
    }
};

