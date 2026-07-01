<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scorecards', function (Blueprint $table) {
            $table->enum('recommended_conclusion', ['pass', 'fail', 'hold'])->nullable()->after('average_score');
            $table->text('override_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('scorecards', function (Blueprint $table) {
            $table->dropColumn(['recommended_conclusion', 'override_reason']);
        });
    }
};
