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

        // ── Chart 1: Last 7 days attendance (bar) ──────────────────────────
        $last7 = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'label' => now()->subDays($daysAgo)->format('D'),
                'date'  => $date,
                'count' => AttendanceRecord::whereDate('arrived_at', $date)
                               ->where('scan_result', 'success')
                               ->count(),
            ];
        });

        // ── Chart 2: Method breakdown today (doughnut) ─────────────────────
        $methodBreakdown = AttendanceRecord::whereDate('arrived_at', today())
            ->where('scan_result', 'success')
            ->selectRaw('method, count(*) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        // ── Chart 3: Attendance by hour today (line) ───────────────────────
        $byHour = AttendanceRecord::whereDate('arrived_at', today())
            ->where('scan_result', 'success')
            ->selectRaw('HOUR(arrived_at) as hour, count(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $hourLabels = collect(range(6, 22))->map(fn($h) => ($h % 12 ?: 12) . ($h < 12 ? 'AM' : 'PM'));
        $hourData   = collect(range(6, 22))->map(fn($h) => $byHour->get($h, 0));

        return view('admin.dashboard', compact(
            'stats', 'recentAttendance', 'last7', 'methodBreakdown', 'hourLabels', 'hourData'
        ));
    }

    /** Real-time polling endpoint */
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

        // Live chart data for today
        $byHour = AttendanceRecord::whereDate('arrived_at', today())
            ->where('scan_result', 'success')
            ->selectRaw('HOUR(arrived_at) as hour, count(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');
        $hourData = collect(range(6, 22))->map(fn($h) => $byHour->get($h, 0));

        $methodBreakdown = AttendanceRecord::whereDate('arrived_at', today())
            ->where('scan_result', 'success')
            ->selectRaw('method, count(*) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        return response()->json(compact('stats', 'recent', 'hourData', 'methodBreakdown'));
    }
}
