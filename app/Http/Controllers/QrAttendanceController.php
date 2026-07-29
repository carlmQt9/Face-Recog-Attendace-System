<?php

namespace App\Http\Controllers;

use App\Mail\AttendanceAlert;
use App\Mail\AttendanceTimeIn;
use App\Mail\AttendanceTimeOut;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QrAttendanceController extends Controller
{
    /**
     * Called by the teacher's camera JS when it decodes a student QR code.
     * POST /attend/student/{token}
     *
     * Payload: { session_id, camera_id }
     * Returns JSON compatible with the existing face-scan response format.
     */
    public function markByToken(Request $request, string $token)
    {
        $request->validate([
            'session_id' => 'required|exists:class_sessions,id',
            'camera_id'  => 'required|exists:cameras,id',
            'scan_type'  => 'nullable|in:time_in,time_out',
        ]);

        $student = Student::with(['user', 'parent'])
            ->where('qr_token', $token)
            ->first();

        if (!$student) {
            return response()->json(['result' => 'error', 'message' => 'QR code not recognised.'], 404);
        }

        $session  = ClassSession::findOrFail($request->session_id);
        $scanType = $request->input('scan_type', 'time_in');

        if (!$session->isActive()) {
            return response()->json(['result' => 'error', 'message' => 'Session is no longer active.'], 422);
        }

        // ── Enforce: student must belong to the session's teacher ─────────────
        if ($student->teacher_id !== null && $student->teacher_id !== $session->teacher_id) {
            return response()->json([
                'result'  => 'error',
                'message' => "{$student->user->name} is not enrolled in this teacher's class.",
            ], 403);
        }

        // ── TIME OUT via QR ───────────────────────────────────────────────────
        if ($scanType === 'time_out') {
            $record = AttendanceRecord::where('student_id', $student->id)
                ->where('scan_type', 'time_in')
                ->whereNull('time_out')
                ->whereDate('arrived_at', today())
                ->where('class_session_id', $session->id)
                ->latest('arrived_at')
                ->first();

            if (!$record) {
                return response()->json([
                    'result'       => 'error',
                    'student_name' => $student->user->name,
                    'message'      => "{$student->user->name} has no open time-in record for this session.",
                ]);
            }

            $record->update(['time_out' => now()]);
            $record->refresh();

            if ($student->parent && !$record->time_out_notification_sent) {
                Mail::to($student->parent->gmail)->send(new AttendanceTimeOut($record));
                $record->update(['time_out_notification_sent' => true]);
            }

            return response()->json([
                'result'       => 'success',
                'scan_type'    => 'time_out',
                'student_id'   => $student->id,
                'student_name' => $student->user->name,
                'arrived_at'   => $record->arrived_at->format('h:i A'),
                'time_out'     => $record->time_out->format('h:i A'),
                'duration'     => $record->durationLabel(),
                'method'       => 'qr_code',
                'message'      => "{$student->user->name} timed out ({$record->durationLabel()}). Parent notified.",
            ]);
        }

        // ── TIME IN via QR ────────────────────────────────────────────────────
        $alreadyIn = AttendanceRecord::where('student_id', $student->id)
            ->where('class_session_id', $session->id)
            ->where('scan_type', 'time_in')
            ->whereNull('time_out')
            ->whereDate('arrived_at', today())
            ->exists();

        if ($alreadyIn) {
            return response()->json([
                'result'       => 'already_in',
                'student_name' => $student->user->name,
                'message'      => "{$student->user->name} already timed in. Switch to Time-Out mode.",
            ]);
        }

        $record = AttendanceRecord::create([
            'student_id'       => $student->id,
            'class_session_id' => $session->id,
            'camera_id'        => $request->camera_id,
            'scan_result'      => 'success',
            'scan_type'        => 'time_in',
            'method'           => 'qr_code',
            'confidence_score' => null,
            'arrived_at'       => now(),
        ]);

        if ($student->parent) {
            Mail::to($student->parent->gmail)->send(new AttendanceTimeIn($record));
            $record->update(['notification_sent' => true]);
        }

        return response()->json([
            'result'       => 'success',
            'scan_type'    => 'time_in',
            'student_id'   => $student->id,
            'student_name' => $student->user->name,
            'arrived_at'   => $record->arrived_at->format('h:i A'),
            'method'       => 'qr_code',
            'message'      => "{$student->user->name} timed in via QR. Parent notified.",
        ]);
    }

    /**
     * Printable QR card for admin.
     * GET /admin/students/{student}/qr-print
     */
    public function printCard(Student $student)
    {
        $student->load('user');
        return view('qr.print-card', compact('student'));
    }
}
