<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','hr','director','pm','leader','candidate') NOT NULL DEFAULT 'pm'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'pm' WHERE role = 'candidate'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','hr','director','pm','leader') NOT NULL DEFAULT 'pm'");
    }
};
