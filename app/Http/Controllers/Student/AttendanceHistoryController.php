<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $records = AttendanceRecord::where('student_id', $student->id)
            ->where('scan_result', 'success')
            ->whereMonth('arrived_at', $month)
            ->whereYear('arrived_at', $year)
            ->with('camera')
            ->orderBy('arrived_at')
            ->get();

        // Group by date for calendar display
        $calendar = $records->groupBy(fn($r) => $r->arrived_at->toDateString());

        if ($request->wantsJson()) {
            return response()->json([
                'total'   => $records->count(),
                'records' => $records->map(fn($r) => [
                    'date'      => $r->arrived_at->format('D, M j'),
                    'time_in'   => $r->arrived_at->format('h:i A'),
                    'time_out'  => $r->time_out?->format('h:i A') ?? '—',
                    'duration'  => $r->durationLabel(),
                    'camera'    => $r->camera->location,
                    'scan_type' => $r->scan_type,
                ]),
            ]);
        }

        return view('student.attendance-history', compact('records', 'calendar', 'month', 'year'));
    }
}
