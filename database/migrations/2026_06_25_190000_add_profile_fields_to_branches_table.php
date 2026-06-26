<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->text('description')->nullable()->after('email_contact');
            $table->unsignedInteger('employee_count')->nullable()->after('description');
            $table->string('website')->nullable()->after('employee_count');
            $table->string('facebook_url')->nullable()->after('website');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('linkedin_url')->nullable()->after('twitter_url');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'employee_count',
                'website',
                'facebook_url',
                'twitter_url',
                'linkedin_url',
            ]);
        });
    }
};
