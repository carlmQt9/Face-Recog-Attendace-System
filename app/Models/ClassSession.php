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
            'morning_in'    => '🌅 Morning — Time In',
            'afternoon_out' => '🌇 Afternoon — Time Out',
            default         => ucfirst($this->session_type ?? ''),
        };
    }

    /** Which scan_type this session defaults to */
    public function defaultScanType(): string
    {
        return $this->session_type === 'afternoon_out' ? 'time_out' : 'time_in';
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
