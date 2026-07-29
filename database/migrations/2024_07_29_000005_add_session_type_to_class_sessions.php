<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            // morning_in  = AM session, students scan Time-In
            // afternoon_out = PM session, students scan Time-Out
            $table->enum('session_type', ['morning_in', 'afternoon_out'])
                  ->default('morning_in')
                  ->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('session_type');
        });
    }
};
