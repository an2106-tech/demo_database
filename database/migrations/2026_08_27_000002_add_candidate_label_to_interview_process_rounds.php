<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_process_template_rounds', function (Blueprint $table) {
            $table->string('candidate_label', 150)
                ->nullable()
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('interview_process_template_rounds', function (Blueprint $table) {
            $table->dropColumn('candidate_label');
        });
    }
};
