<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use App\Mail\AttendanceAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ManualAttendanceController extends Controller
{
    /**
     * Manually mark a student as present within an active class session.
     */
    public function store(Request $request, ClassSession $session)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        // Check if already marked
        $exists = AttendanceRecord::where('student_id', $request->student_id)
            ->where('class_session_id', $session->id)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Student is already marked present in this session.');
        }

        $record = AttendanceRecord::create([
            'student_id'       => $request->student_id,
            'class_session_id' => $session->id,
            'camera_id'        => $session->camera_id,
            'scan_result'      => 'success',
            'method'           => 'manual',
            'marked_by'        => auth()->user()->name,
            'arrived_at'       => now(),
        ]);

        // Send parent notification
        $student = Student::with(['user', 'parent'])->find($request->student_id);

        if ($student->parent) {
            Mail::to($student->parent->gmail)->send(new AttendanceAlert($record));
            $record->update(['notification_sent' => true]);
        }

        return back()->with('success', "{$student->user->name} manually marked as present.");
    }
}
