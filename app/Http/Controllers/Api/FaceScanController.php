<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AttendanceAlert;
use App\Models\AttendanceRecord;
use App\Models\Camera;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FaceScanController extends Controller
{
    /**
     * Process an incoming face scan event from a camera device.
     *
     * Expected JSON payload:
     * {
     *   "camera_id": 1,
     *   "student_id": 5,          // resolved by Python face-recognition service
     *   "confidence_score": 94.5,
     *   "scan_result": "success"  // "success" or "error"
     * }
     */
    public function process(Request $request)
    {
        $request->validate([
            'camera_id'        => 'required|exists:cameras,id',
            'scan_result'      => 'required|in:success,error',
            'student_id'       => 'nullable|exists:students,id',
            'confidence_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $camera = Camera::findOrFail($request->camera_id);

        if (!$camera->is_active) {
            return response()->json(['message' => 'Camera is not active.'], 422);
        }

        // If scan failed (unrecognized), log the error event and return 2-beep signal
        if ($request->scan_result === 'error' || !$request->student_id) {
            return response()->json([
                'result'  => 'error',
                'signal'  => 'double_beep',
                'message' => 'Face not recognized. Please adjust position or ask teacher for help.',
            ]);
        }

        $student = Student::with(['user', 'parent'])->findOrFail($request->student_id);

        // Find the active class session for this camera
        $session = ClassSession::where('camera_id', $camera->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        // Enforce cool-down: prevent duplicate scan within the configured window
        $cooldown = (int) SystemSetting::get('cooldown_seconds', 5);

        $recentScan = AttendanceRecord::where('student_id', $student->id)
            ->when($session, fn($q) => $q->where('class_session_id', $session->id))
            ->where('arrived_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recentScan) {
            return response()->json([
                'result'  => 'cooldown',
                'signal'  => 'double_beep',
                'message' => "System is in cool-down. Please wait {$cooldown} seconds.",
            ]);
        }

        // Record attendance
        $record = AttendanceRecord::create([
            'student_id'        => $student->id,
            'class_session_id'  => $session?->id,
            'camera_id'         => $camera->id,
            'scan_result'       => 'success',
            'method'            => 'face_scan',
            'confidence_score'  => $request->confidence_score,
            'arrived_at'        => now(),
        ]);

        // Send parent email notification
        if ($student->parent) {
            Mail::to($student->parent->gmail)->send(new AttendanceAlert($record));
            $record->update(['notification_sent' => true]);
        }

        return response()->json([
            'result'       => 'success',
            'signal'       => 'single_beep',
            'student_name' => $student->user->name,
            'arrived_at'   => $record->arrived_at->format('h:i A'),
            'message'      => "{$student->user->name} has been marked present. Parent notified.",
        ]);
    }
}
