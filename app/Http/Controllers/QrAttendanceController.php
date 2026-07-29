<?php

namespace App\Http\Controllers;

use App\Mail\AttendanceAlert;
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
        ]);

        $student = Student::with(['user', 'parent'])
            ->where('qr_token', $token)
            ->first();

        if (!$student) {
            return response()->json([
                'result'  => 'error',
                'message' => 'QR code not recognised.',
            ], 404);
        }

        $session = ClassSession::findOrFail($request->session_id);

        if (!$session->isActive()) {
            return response()->json([
                'result'  => 'error',
                'message' => 'Session is no longer active.',
            ], 422);
        }

        // Prevent duplicate mark within the same session
        $alreadyMarked = AttendanceRecord::where('student_id', $student->id)
            ->where('class_session_id', $session->id)
            ->exists();

        if ($alreadyMarked) {
            return response()->json([
                'result'       => 'cooldown',
                'student_name' => $student->user->name,
                'message'      => "{$student->user->name} is already marked present.",
            ]);
        }

        // Record attendance
        $record = AttendanceRecord::create([
            'student_id'       => $student->id,
            'class_session_id' => $session->id,
            'camera_id'        => $request->camera_id,
            'scan_result'      => 'success',
            'method'           => 'qr_code',
            'confidence_score' => null,
            'arrived_at'       => now(),
        ]);

        // Notify parent
        if ($student->parent) {
            Mail::to($student->parent->gmail)->send(new AttendanceAlert($record));
            $record->update(['notification_sent' => true]);
        }

        return response()->json([
            'result'       => 'success',
            'student_id'   => $student->id,
            'student_name' => $student->user->name,
            'arrived_at'   => $record->arrived_at->format('h:i A'),
            'method'       => 'qr_code',
            'message'      => "{$student->user->name} marked present via QR.",
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
