<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AttendanceTimeIn;
use App\Mail\AttendanceTimeOut;
use App\Models\AttendanceRecord;
use App\Models\Camera;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class FaceScanController extends Controller
{
    /**
     * Process a face/QR scan — handles both time_in and time_out.
     *
     * Payload:
     * {
     *   "camera_id": 1,
     *   "student_id": 5,
     *   "scan_type": "time_in"|"time_out",   // default: time_in
     *   "session_id": 2,
     *   "confidence_score": 94.5,
     *   "face_image": "base64..."
     * }
     */
    public function process(Request $request)
    {
        $request->validate([
            'camera_id'        => 'required|exists:cameras,id',
            'scan_result'      => 'nullable|in:success,error',
            'scan_type'        => 'nullable|in:time_in,time_out',
            'student_id'       => 'nullable|exists:students,id',
            'confidence_score' => 'nullable|numeric|min:0|max:100',
            'face_image'       => 'nullable|string',
            'session_id'       => 'nullable|exists:class_sessions,id',
        ]);

        $camera = Camera::findOrFail($request->camera_id);

        if (!$camera->is_active) {
            return response()->json(['message' => 'Camera is not active.'], 422);
        }

        // Unrecognised face
        if ($request->scan_result === 'error' || !$request->student_id) {
            return response()->json([
                'result'  => 'error',
                'signal'  => 'double_beep',
                'message' => 'Face not recognized.',
            ]);
        }

        $student  = Student::with(['user', 'parent'])->findOrFail($request->student_id);
        $scanType = $request->input('scan_type', 'time_in');

        // Find the active class session
        $session = $request->session_id
            ? ClassSession::find($request->session_id)
            : ClassSession::where('camera_id', $camera->id)->where('status', 'active')->latest()->first();

        // ── Enforce: student must belong to the session's teacher ─────────────
        if ($session && $student->teacher_id !== null && $student->teacher_id !== $session->teacher_id) {
            return response()->json([
                'result'  => 'error',
                'signal'  => 'double_beep',
                'message' => "{$student->user->name} is not enrolled in this teacher's class.",
            ]);
        }

        // ── TIME OUT ─────────────────────────────────────────────────────────
        if ($scanType === 'time_out') {
            return $this->handleTimeOut($student, $session, $camera, $request);
        }

        // ── TIME IN ──────────────────────────────────────────────────────────
        return $this->handleTimeIn($student, $session, $camera, $request);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function handleTimeIn($student, $session, $camera, $request)
    {
        $cooldown = (int) SystemSetting::get('cooldown_seconds', 5);

        // Check if already timed-in today in this session (no time-out yet)
        $existing = AttendanceRecord::where('student_id', $student->id)
            ->when($session, fn($q) => $q->where('class_session_id', $session->id))
            ->where('scan_type', 'time_in')
            ->whereNull('time_out')
            ->whereDate('arrived_at', today())
            ->first();

        if ($existing) {
            return response()->json([
                'result'       => 'already_in',
                'student_name' => $student->user->name,
                'arrived_at'   => $existing->arrived_at->format('h:i A'),
                'message'      => "{$student->user->name} already timed in at {$existing->arrived_at->format('h:i A')}. Use Time-Out mode to log departure.",
            ]);
        }

        // Cooldown check (prevent double-tap within seconds)
        $recentScan = AttendanceRecord::where('student_id', $student->id)
            ->where('arrived_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recentScan) {
            return response()->json([
                'result'  => 'cooldown',
                'signal'  => 'double_beep',
                'message' => "Cool-down active. Please wait {$cooldown} seconds.",
            ]);
        }

        $record = AttendanceRecord::create([
            'student_id'        => $student->id,
            'class_session_id'  => $session?->id,
            'camera_id'         => $camera->id,
            'scan_result'       => 'success',
            'scan_type'         => 'time_in',
            'status'           => 'present',
            'method'            => 'face_scan',
            'confidence_score'  => $request->confidence_score,
            'snapshot_path'     => $this->saveSnapshot($request->face_image, $student->id, 'in'),
            'arrived_at'        => now(),
        ]);

        // Notify parent — time in
        if ($student->parent) {
            try {
                Mail::to($student->parent->gmail)->send(new AttendanceTimeIn($record));
                $record->update(['notification_sent' => true]);
            } catch (\Exception $e) {
                \Log::error('Time-in email failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'result'       => 'success',
            'scan_type'    => 'time_in',
            'signal'       => 'single_beep',
            'student_name' => $student->user->name,
            'arrived_at'   => $record->arrived_at->format('h:i A'),
            'snapshot_url' => $record->snapshotUrl(),
            'message'      => "{$student->user->name} timed in at {$record->arrived_at->format('h:i A')}.",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function handleTimeOut($student, $session, $camera, $request)
    {
        // ── Check short cooldown to prevent rapid duplicate scans ─────────────
        $cooldown = (int) SystemSetting::get('cooldown_seconds', 5);
        $recentAny = AttendanceRecord::where('student_id', $student->id)
            ->where('updated_at', '>=', now()->subSeconds($cooldown))
            ->exists();

        if ($recentAny) {
            return response()->json([
                'result'  => 'cooldown',
                'signal'  => 'double_beep',
                'message' => "Cool-down active. Please wait {$cooldown} seconds.",
            ]);
        }

        // ── Find open time-in record for today ────────────────────────────────
        $record = AttendanceRecord::where('student_id', $student->id)
            ->where('scan_type', 'time_in')
            ->whereNull('time_out')
            ->whereDate('arrived_at', today())
            ->when($session, fn($q) => $q->where('class_session_id', $session->id))
            ->latest('arrived_at')
            ->first();

        // ── Already timed out today in this session? ──────────────────────────
        $alreadyOut = AttendanceRecord::where('student_id', $student->id)
            ->whereNotNull('time_out')
            ->whereDate('arrived_at', today())
            ->when($session, fn($q) => $q->where('class_session_id', $session->id))
            ->exists();

        if ($alreadyOut && !$record) {
            return response()->json([
                'result'       => 'already_out',
                'student_name' => $student->user->name,
                'message'      => "{$student->user->name} has already timed out today.",
            ]);
        }

        // ── No open time-in — check if student was marked absent in morning ───
        if (!$record) {
            // Check if this is an afternoon session and student was absent in morning
            if ($session && $session->session_type === 'afternoon_out') {
                // Try to update absent status to late
                $wasAbsent = $session->updateAbsentToLate($student->id);
                if ($wasAbsent) {
                    // Create a new time_in record for the afternoon session
                    $record = AttendanceRecord::create([
                        'student_id'        => $student->id,
                        'class_session_id'  => $session?->id,
                        'camera_id'         => $camera->id,
                        'scan_result'       => 'success',
                        'scan_type'         => 'time_in',
                        'status'           => 'late',
                        'method'            => 'face_scan',
                        'confidence_score'  => $request->confidence_score,
                        'snapshot_path'     => $this->saveSnapshot($request->face_image, $student->id, 'late_in'),
                        'arrived_at'        => now()->subMinutes(1),
                    ]);
                }
            }
            
            // If still no record, create one
            if (!$record) {
                $record = AttendanceRecord::create([
                    'student_id'        => $student->id,
                    'class_session_id'  => $session?->id,
                    'camera_id'         => $camera->id,
                    'scan_result'       => 'success',
                    'scan_type'         => 'time_in',
                    'status'           => 'present',
                    'method'            => 'face_scan',
                    'confidence_score'  => $request->confidence_score,
                    'snapshot_path'     => $this->saveSnapshot($request->face_image, $student->id, 'out'),
                    'arrived_at'        => now()->subMinutes(1),
                ]);
            }
        } else {
            // Update snapshot on the existing record if not already set
            if (!$record->snapshot_path && $request->face_image) {
                $record->update([
                    'snapshot_path' => $this->saveSnapshot($request->face_image, $student->id, 'out'),
                ]);
            }
        }

        // ── Record time-out ───────────────────────────────────────────────────
        $record->update(['time_out' => now()]);
        $record->refresh();

        if ($student->parent && !$record->time_out_notification_sent) {
            try {
                Mail::to($student->parent->gmail)->send(new AttendanceTimeOut($record));
                $record->update(['time_out_notification_sent' => true]);
            } catch (\Exception $e) {
                \Log::error('Time-out email failed: ' . $e->getMessage());
            }
        }

        $statusMessage = $record->status === 'late' ? " (marked as late due to morning absence)" : "";

        return response()->json([
            'result'       => 'success',
            'scan_type'    => 'time_out',
            'signal'       => 'single_beep',
            'student_name' => $student->user->name,
            'arrived_at'   => $record->arrived_at->format('h:i A'),
            'time_out'     => $record->time_out->format('h:i A'),
            'duration'     => $record->durationLabel(),
            'status'       => $record->status,
            'snapshot_url' => $record->snapshotUrl(),
            'message'      => "{$student->user->name} timed out at {$record->time_out->format('h:i A')} (stayed {$record->durationLabel()}){$statusMessage}.",
        ]);
    }

    /**
     * Save base64 face_image to public/snapshots/ and return path.
     * Uses public_path() so no storage symlink is needed — works on localhost AND InfinityFree.
     */
    private function saveSnapshot(?string $base64, int $studentId, string $type): ?string
    {
        if (!$base64 || strlen($base64) < 100) {
            \Log::warning("Attendance snapshot missing or invalid for student {$studentId}, type: {$type}");
            return null;
        }

        try {
            $data    = preg_replace('/^data:image\/[a-zA-Z+]+;base64,/', '', $base64);
            $data    = str_replace([' ', "\n", "\r"], ['+', '', ''], $data);
            $decoded = base64_decode($data, strict: true);
            if ($decoded === false) {
                \Log::warning("Base64 decode failed for student {$studentId}, type: {$type}");
                return null;
            }

            $path     = 'snapshots/student_' . $studentId . '_' . $type . '_' . time() . '.jpg';
            $fullPath = public_path($path);
            $dir      = dirname($fullPath);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($fullPath, $decoded);

            if (!file_exists($fullPath)) {
                \Log::error("Snapshot file was not saved successfully: {$path}");
                return null;
            }

            \Log::info("Attendance snapshot saved successfully: {$path}");
            return $path;
        } catch (\Exception $e) {
            \Log::error('Snapshot save failed for student ' . $studentId . ': ' . $e->getMessage());
            return null;
        }
    }
}
