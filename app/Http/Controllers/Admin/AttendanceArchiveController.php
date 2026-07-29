<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Camera;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['student.user', 'camera', 'classSession'])
            ->orderBy('arrived_at', 'desc');

        // Filters
        if ($request->filled('date')) {
            $query->whereDate('arrived_at', $request->date);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('camera_id')) {
            $query->where('camera_id', $request->camera_id);
        }

        $records  = $query->paginate(30)->withQueryString();
        $students = Student::with('user')->orderBy('id')->get();
        $cameras  = Camera::orderBy('name')->get();

        return view('admin.attendance.index', compact('records', 'students', 'cameras'));
    }

    public function export(Request $request)
    {
        $query = AttendanceRecord::with(['student.user', 'camera'])
            ->where('scan_result', 'success')
            ->orderBy('arrived_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('arrived_at', $request->date);
        }

        $records = $query->get();

        $csv = "Student Name,Student ID,Camera/Location,Arrived At,Method\n";

        foreach ($records as $record) {
            $csv .= implode(',', [
                '"' . $record->student->user->name . '"',
                $record->student->student_id,
                '"' . $record->camera->location . '"',
                $record->arrived_at->format('Y-m-d H:i:s'),
                $record->method,
            ]) . "\n";
        }

        $filename = 'attendance_' . ($request->date ?? today()->toDateString()) . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
