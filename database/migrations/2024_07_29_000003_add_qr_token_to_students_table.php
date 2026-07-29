<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Student;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('face_registered');
        });

        // Backfill any existing students that don't have a token yet
        Student::whereNull('qr_token')->each(function ($student) {
            $student->update(['qr_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
