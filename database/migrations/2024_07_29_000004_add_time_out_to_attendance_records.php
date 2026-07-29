<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Time-out timestamp (null until student scans out)
            $table->timestamp('time_out')->nullable()->after('arrived_at');
            // Whether the time-out parent notification was sent
            $table->boolean('time_out_notification_sent')->default(false)->after('notification_sent');
            // Rename arrived_at to time_in conceptually — keep column name for BC
            // Add a scan_type column so one record holds both in & out
            $table->enum('scan_type', ['time_in', 'time_out'])->default('time_in')->after('scan_result');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['time_out', 'time_out_notification_sent', 'scan_type']);
        });
    }
};
