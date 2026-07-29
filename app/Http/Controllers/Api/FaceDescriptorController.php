<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceDescriptorController extends Controller
{
    /**
     * Return registered face images for the students in a specific session's teacher class.
     * Only students belonging to the session's teacher are returned.
     *
     * GET /api/face-descriptors?session_id={id}
     */
    public function index(Request $request)
    {
        // If a session_id is provided, scope to that teacher's students only
        $query = Student::with('user')
            ->where('face_registered', true)
            ->whereNotNull('face_encoding');

        if ($request->filled('session_id')) {
            $session = ClassSession::find($request->session_id);
            if ($session && $session->teacher_id) {
                $query->where('teacher_id', $session->teacher_id);
            }
        }

        $students = $query->get()->map(function ($student) {
            $images = [];

            // Primary image
            if ($student->face_encoding && Storage::disk('public')->exists($student->face_encoding)) {
                $images[] = Storage::url($student->face_encoding);
            }

            // Extra samples: left, right, blink
            $suffixes = ['left', 'right', 'blink'];
            $files    = Storage::disk('public')->files('faces');
            foreach ($suffixes as $suffix) {
                foreach ($files as $file) {
                    if (str_starts_with($file, "faces/student_{$student->id}_{$suffix}_")) {
                        $images[] = Storage::url($file);
                        break;
                    }
                }
            }

            return [
                'student_id'   => $student->id,
                'student_name' => $student->user->name,
                'student_code' => $student->student_id,
                'face_images'  => $images,
            ];
        })
        ->filter(fn($s) => count($s['face_images']) > 0)
        ->values();

        return response()->json([
            'students' => $students,
            'total'    => $students->count(),
        ]);
    }
}
