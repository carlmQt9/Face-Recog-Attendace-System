<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'camera_id',
        'subject',
        'section',
        'session_type',
        'scheduled_start',
        'scheduled_end',
        'started_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /** Human-readable label for the session type */
    public function sessionTypeLabel(): string
    {
        return match($this->session_type) {
            'morning_in'    => '📥 Time In',
            'afternoon_out' => '📤 Time Out',
            default         => ucfirst($this->session_type ?? ''),
        };
    }

    /** Which scan_type this session defaults to */
    public function defaultScanType(): string
    {
        return $this->session_type === 'afternoon_out' ? 'time_out' : 'time_in';
    }

    /** Check if session is currently active */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Mark absent students when morning session ends */
    public function markAbsentStudents(): int
    {
        if ($this->session_type !== 'morning_in') {
            return 0; // Only mark absent for morning sessions
        }

        // Get all students under this teacher
        $teacherStudents = \App\Models\Student::where('teacher_id', $this->teacher_id)->pluck('id');

        // Get students who already have attendance records for this session
        $presentStudentIds = \App\Models\AttendanceRecord::where('class_session_id', $this->id)
            ->whereIn('student_id', $teacherStudents)
            ->pluck('student_id')
            ->unique();

        // Find students who didn't attend
        $absentStudentIds = $teacherStudents->diff($presentStudentIds);

        $markedCount = 0;
        foreach ($absentStudentIds as $studentId) {
            \App\Models\AttendanceRecord::create([
                'student_id' => $studentId,
                'class_session_id' => $this->id,
                'camera_id' => $this->camera_id,
                'scan_result' => 'success',
                'scan_type' => 'time_in',
                'status' => 'absent',
                'method' => 'system',
                'marked_by' => 'System - Auto Absent',
                'arrived_at' => $this->ended_at ?? now(),
            ]);
            $markedCount++;
        }

        return $markedCount;
    }

    /** Update absent students to late if they show up for time_out */
    public function updateAbsentToLate($studentId): bool
    {
        // Find the absent record from morning session
        $morningSession = self::where('teacher_id', $this->teacher_id)
            ->where('session_type', 'morning_in')
            ->whereDate('started_at', today())
            ->first();

        if ($morningSession) {
            $absentRecord = \App\Models\AttendanceRecord::where('class_session_id', $morningSession->id)
                ->where('student_id', $studentId)
                ->where('status', 'absent')
                ->first();

            if ($absentRecord) {
                $absentRecord->update([
                    'status' => 'late',
                    'marked_by' => 'System - Late Arrival',
                ]);
                return true;
            }
        }

        return false;
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
