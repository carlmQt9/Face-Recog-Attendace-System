<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Camera;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_students'    => Student::count(),
            'total_teachers'    => Teacher::count(),
            'total_cameras'     => Camera::count(),
            'active_cameras'    => Camera::where('is_active', true)->count(),
            'today_attendance'  => AttendanceRecord::whereDate('arrived_at', today())
                                        ->where('scan_result', 'success')
                                        ->count(),
        ];

        $recentAttendance = AttendanceRecord::with(['student.user', 'camera'])
            ->where('scan_result', 'success')
            ->latest('arrived_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendance'));
    }
}
