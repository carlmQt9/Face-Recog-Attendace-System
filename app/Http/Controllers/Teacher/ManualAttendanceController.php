<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Mail\AttendanceTimeIn;
use App\Mail\AttendanceTimeOut;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ManualAttendanceController extends Controller
{
    public function store(Request $request, ClassSession $session)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'scan_type'  => 'nullable|in:time_in,time_out',
        ]);

        $scanType = $request->input('scan_type', 'time_in');
        $student  = Student::with(['user', 'parent'])->findOrFail($request->student_id);

        // Only allow the teacher's own students
        if ($student->teacher_id !== null && $student->teacher_id !== $session->teacher_id) {
            return back()->with('error', "{$student->user->name} is not in your class.");
        }

        // ── TIME OUT ──────────────────────────────────────────────────────────
        if ($scanType === 'time_out') {
            // First, check if student has an open time-in record today
            $record = AttendanceRecord::where('student_id', $student->id)
                ->where('class_session_id', $session->id)
                ->where('scan_type', 'time_in')
                ->whereNull('time_out')
                ->whereDate('arrived_at', today())
                ->latest('arrived_at')
                ->first();

            if ($record) {
                // Student has a time-in: mark them out normally
                $record->update(['time_out' => now(), 'marked_by' => auth()->user()->name]);
                $record->refresh();

                if ($student->parent && !$record->time_out_notification_sent) {
                    Mail::to($student->parent->gmail)->send(new AttendanceTimeOut($record));
                    $record->update(['time_out_notification_sent' => true]);
                }

                return back()->with('success',
                    "{$student->user->name} marked out at {$record->time_out->format('h:i A')} (stayed {$record->durationLabel()}).");
            } else {
                // Student has NO time-in: allow teacher to create a time-out only record
                // This is for students who arrive after the session starts or join mid-way
                $record = AttendanceRecord::create([
                    'student_id'       => $student->id,
                    'class_session_id' => $session->id,
                    'camera_id'        => $session->camera_id,
                    'scan_result'      => 'success',
                    'scan_type'        => 'time_out',
                    'method'           => 'manual',
                    'marked_by'        => auth()->user()->name,
                    'time_out'         => now(),
                    // No arrived_at for time-out only record
                ]);

                if ($student->parent) {
                    Mail::to($student->parent->gmail)->send(new AttendanceTimeOut($record));
                    $record->update(['time_out_notification_sent' => true]);
                }

                return back()->with('success',
                    "{$student->user->name} marked out at {$record->time_out->format('h:i A')} (without prior time-in).");
            }
        }

        // ── TIME IN ───────────────────────────────────────────────────────────
        $alreadyIn = AttendanceRecord::where('student_id', $student->id)
            ->where('class_session_id', $session->id)
            ->where('scan_type', 'time_in')
            ->whereNull('time_out')
            ->whereDate('arrived_at', today())
            ->exists();

        if ($alreadyIn) {
            return back()->with('warning', "{$student->user->name} is already timed in. Use Time-Out to log departure.");
        }

        $record = AttendanceRecord::create([
            'student_id'       => $student->id,
            'class_session_id' => $session->id,
            'camera_id'        => $session->camera_id,
            'scan_result'      => 'success',
            'scan_type'        => 'time_in',
            'method'           => 'manual',
            'marked_by'        => auth()->user()->name,
            'arrived_at'       => now(),
        ]);

        if ($student->parent) {
            Mail::to($student->parent->gmail)->send(new AttendanceTimeIn($record));
            $record->update(['notification_sent' => true]);
        }

        return back()->with('success', "{$student->user->name} marked in at {$record->arrived_at->format('h:i A')}.");
    }
}
