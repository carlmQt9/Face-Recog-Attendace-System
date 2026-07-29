<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_session_id',
        'camera_id',
        'scan_result',
        'scan_type',
        'method',
        'marked_by',
        'confidence_score',
        'arrived_at',
        'time_out',
        'notification_sent',
        'time_out_notification_sent',
    ];

    protected $casts = [
        'arrived_at'                 => 'datetime',
        'time_out'                   => 'datetime',
        'notification_sent'          => 'boolean',
        'time_out_notification_sent' => 'boolean',
        'confidence_score'           => 'decimal:2',
    ];

    /** Duration in minutes between time-in and time-out */
    public function durationMinutes(): ?int
    {
        if ($this->arrived_at && $this->time_out) {
            return (int) $this->arrived_at->diffInMinutes($this->time_out);
        }
        return null;
    }

    /** Human-readable duration e.g. "1h 23m" */
    public function durationLabel(): string
    {
        $mins = $this->durationMinutes();
        if ($mins === null) return '—';
        if ($mins < 60)    return "{$mins}m";
        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }
}
