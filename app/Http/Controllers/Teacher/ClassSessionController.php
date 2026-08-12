<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    public function index()
    {
        $teacher  = auth()->user()->teacher;
        $sessions = ClassSession::with('camera')
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("FIELD(status, 'active', 'ended') ASC")  // Active sessions first
            ->latest()  // Then by newest first
            ->paginate(15);

        $cameras = Camera::where('is_active', true)->get();

        return view('teacher.sessions.index', compact('sessions', 'cameras'));
    }

    /** Real-time polling — returns latest sessions list as JSON */
    public function sessionStats()
    {
        $teacher  = auth()->user()->teacher;
        $sessions = ClassSession::with('camera')
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("FIELD(status, 'active', 'ended') ASC")  // Active sessions first
            ->latest()  // Then by newest first
            ->take(15)
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'subject'         => $s->subject,
                'section'         => $s->section,
                'session_type'    => $s->session_type,
                'status'          => $s->status,
                'started_at'      => $s->started_at?->format('M d, h:i A') ?? '—',
                'ended_at'        => $s->ended_at?->format('h:i A') ?? '—',
                'scheduled_start' => $s->scheduled_start ? \Carbon\Carbon::parse($s->scheduled_start)->format('h:i A') : null,
                'scheduled_end'   => $s->scheduled_end   ? \Carbon\Carbon::parse($s->scheduled_end)->format('h:i A')   : null,
                'camera'          => $s->camera->location,
                'is_local'        => $s->camera->is_local_device,
                'camera_route'    => $s->status === 'active' ? route('teacher.sessions.camera', $s->id) : null,
                'live_route'      => route('teacher.sessions.live', $s->id),
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'camera_id'        => 'required|exists:cameras,id',
            'subject'          => 'required|string|max:255',
            'section'          => 'required|string|max:100',
            'session_type'     => 'required|in:morning_in,afternoon_out',
            'scheduled_start'  => 'nullable|date_format:H:i',
            'scheduled_end'    => 'nullable|date_format:H:i|after:scheduled_start',
        ]);

        $teacher = auth()->user()->teacher;

        // End any previously active session for this teacher
        ClassSession::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->update(['status' => 'ended', 'ended_at' => now()]);

        $session = ClassSession::create([
            'teacher_id'      => $teacher->id,
            'camera_id'       => $request->camera_id,
            'subject'         => $request->subject,
            'section'         => $request->section,
            'session_type'    => $request->session_type,
            'scheduled_start' => $request->scheduled_start ?: null,
            'scheduled_end'   => $request->scheduled_end   ?: null,
            'started_at'      => now(),
            'status'          => 'active',
        ]);

        $session->camera->update(['is_active' => true]);

        return redirect()->route('teacher.sessions.camera', $session->id)
            ->with('success', 'Session started — ' . $session->sessionTypeLabel());
    }

    /**
     * Called by the camera page via JS polling to check if the session
     * has passed its scheduled end time and should auto-end.
     */
    public function checkSchedule(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        if (
            $session->isActive()
            && $session->scheduled_end
            && now()->format('H:i') >= $session->scheduled_end
        ) {
            $session->update(['status' => 'ended', 'ended_at' => now()]);
            $session->camera->update(['is_active' => false]);
            
            // Mark absent students when morning session auto-ends
            $absentCount = $session->markAbsentStudents();

            return response()->json([
                'auto_ended' => true,
                'absent_count' => $absentCount
            ]);
        }

        return response()->json([
            'auto_ended'     => false,
            'scheduled_end'  => $session->scheduled_end,
            'server_time'    => now()->format('H:i'),
        ]);
    }

    public function live(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        $attendance = $session->attendanceRecords()
            ->with('student.user')
            ->orderBy('arrived_at')
            ->get();

        // Only show the teacher's own students in the manual mark dropdown
        $students = \App\Models\Student::with('user')
            ->where('teacher_id', $session->teacher_id)
            ->orderBy('id')->get();

        return view('teacher.sessions.live', compact('session', 'attendance', 'students'));
    }

    public function camera(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        $attendance = $session->attendanceRecords()
            ->with('student.user')
            ->orderBy('arrived_at')
            ->get();

        // Only the teacher's own students for manual override dropdown
        $students = \App\Models\Student::with('user')
            ->where('teacher_id', $session->teacher_id)
            ->orderBy('id')->get();

        return view('teacher.sessions.camera', compact('session', 'attendance', 'students'));
    }

    public function stop(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        $session->update(['status' => 'ended', 'ended_at' => now()]);
        
        // Mark absent students when morning session ends
        $absentCount = $session->markAbsentStudents();
        
        $message = 'Class session ended.';
        if ($absentCount > 0) {
            $message .= " {$absentCount} students marked as absent.";
        }

        return redirect()->route('teacher.sessions.index')
            ->with('success', $message);
    }

    private function authorizeTeacher(ClassSession $session): void
    {
        if ($session->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized.');
        }
    }
}
