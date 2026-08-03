<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            // e.g. "07:00" and "08:30"
            $table->time('scheduled_start')->nullable()->after('section');
            $table->time('scheduled_end')->nullable()->after('scheduled_start');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn(['scheduled_start', 'scheduled_end']);
        });
    }
};
