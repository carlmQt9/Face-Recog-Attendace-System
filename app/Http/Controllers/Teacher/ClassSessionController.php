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
            ->latest()
            ->paginate(15);

        $cameras = Camera::where('is_active', true)->get();

        return view('teacher.sessions.index', compact('sessions', 'cameras'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'camera_id' => 'required|exists:cameras,id',
            'subject'   => 'required|string|max:255',
            'section'   => 'required|string|max:100',
        ]);

        $teacher = auth()->user()->teacher;

        // End any previously active session for this teacher
        ClassSession::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->update(['status' => 'ended', 'ended_at' => now()]);

        $session = ClassSession::create([
            'teacher_id' => $teacher->id,
            'camera_id'  => $request->camera_id,
            'subject'    => $request->subject,
            'section'    => $request->section,
            'started_at' => now(),
            'status'     => 'active',
        ]);

        // Activate the selected camera
        $session->camera->update(['is_active' => true]);

        return redirect()->route('teacher.sessions.live', $session->id)
            ->with('success', 'Class session started. Camera is now tracking attendance.');
    }

    public function live(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        $attendance = $session->attendanceRecords()
            ->with('student.user')
            ->orderBy('arrived_at')
            ->get();

        return view('teacher.sessions.live', compact('session', 'attendance'));
    }

    public function stop(ClassSession $session)
    {
        $this->authorizeTeacher($session);

        $session->update(['status' => 'ended', 'ended_at' => now()]);

        return redirect()->route('teacher.sessions.index')
            ->with('success', 'Class session ended.');
    }

    private function authorizeTeacher(ClassSession $session): void
    {
        if ($session->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized.');
        }
    }
}
