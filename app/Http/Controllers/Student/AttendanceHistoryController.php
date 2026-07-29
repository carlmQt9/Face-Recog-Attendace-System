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

        return view('student.attendance-history', compact('records', 'calendar', 'month', 'year'));
    }
}
