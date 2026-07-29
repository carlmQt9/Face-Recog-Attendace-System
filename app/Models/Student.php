<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'grade_level',
        'section',
        'face_encoding',
        'face_registered',
        'qr_token',
    ];

    protected $casts = [
        'face_registered' => 'boolean',
    ];

    /**
     * Auto-generate a unique QR token whenever a student is created.
     */
    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->qr_token)) {
                $student->qr_token = Str::random(32);
            }
        });
    }

    /**
     * The full URL encoded in this student's QR code.
     */
    public function qrUrl(): string
    {
        return url("/attend/student/{$this->qr_token}");
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->hasOne(ParentContact::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
