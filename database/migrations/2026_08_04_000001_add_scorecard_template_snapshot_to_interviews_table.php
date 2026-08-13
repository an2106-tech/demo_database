<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->foreignId('scorecard_template_id')
                ->nullable()
                ->after('interviewer_id')
                ->constrained('scorecard_templates')
                ->nullOnDelete();
            $table->json('scorecard_template_snapshot')
                ->nullable()
                ->after('scorecard_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('scorecard_template_id');
            $table->dropColumn('scorecard_template_snapshot');
        });
    }
};
