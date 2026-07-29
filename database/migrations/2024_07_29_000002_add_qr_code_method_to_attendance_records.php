<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: alter the enum to include qr_code
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN method ENUM('face_scan','manual','qr_code') NOT NULL DEFAULT 'face_scan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN method ENUM('face_scan','manual') NOT NULL DEFAULT 'face_scan'");
    }
};
