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
        'method',
        'marked_by',
        'confidence_score',
        'arrived_at',
        'notification_sent',
    ];

    protected $casts = [
        'arrived_at'        => 'datetime',
        'notification_sent' => 'boolean',
        'confidence_score'  => 'decimal:2',
    ];

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
