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

    /** Real-time polling endpoint — returns latest stats + recent attendance as JSON */
    public function stats()
    {
        $stats = [
            'total_students'   => Student::count(),
            'total_teachers'   => Teacher::count(),
            'total_cameras'    => Camera::count(),
            'active_cameras'   => Camera::where('is_active', true)->count(),
            'today_attendance' => AttendanceRecord::whereDate('arrived_at', today())
                                      ->where('scan_result', 'success')
                                      ->count(),
        ];

        $recent = AttendanceRecord::with(['student.user', 'camera'])
            ->where('scan_result', 'success')
            ->latest('arrived_at')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'name'     => $r->student->user->name,
                'camera'   => $r->camera->location,
                'method'   => ucfirst(str_replace('_', ' ', $r->method)),
                'time'     => $r->arrived_at->format('h:i A'),
                'notified' => (bool) $r->notification_sent,
            ]);

        return response()->json(compact('stats', 'recent'));
    }
}
