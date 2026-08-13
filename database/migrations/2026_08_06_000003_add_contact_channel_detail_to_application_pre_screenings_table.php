<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_pre_screenings', function (Blueprint $table) {
            $table->string('contact_channel_detail', 120)->nullable()->after('contact_channel');
        });
    }

    public function down(): void
    {
        Schema::table('application_pre_screenings', function (Blueprint $table) {
            $table->dropColumn('contact_channel_detail');
        });
    }
};
