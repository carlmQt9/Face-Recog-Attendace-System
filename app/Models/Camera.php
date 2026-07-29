<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'type',
        'is_active',
        'device_identifier',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
