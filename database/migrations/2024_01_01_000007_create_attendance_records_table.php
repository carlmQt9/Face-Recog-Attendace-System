<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_session_id')->nullable()->constrained('class_sessions')->onDelete('set null');
            $table->foreignId('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->enum('scan_result', ['success', 'error'])->default('success');
            $table->enum('method', ['face_scan', 'manual'])->default('face_scan');
            $table->string('marked_by')->nullable(); // teacher name if manual
            $table->decimal('confidence_score', 5, 2)->nullable(); // face recognition confidence %
            $table->timestamp('arrived_at');
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
