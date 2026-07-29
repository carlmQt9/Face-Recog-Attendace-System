<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class FaceDescriptorController extends Controller
{
    /**
     * Return all registered students with their face image URLs
     * so the browser can build face-api.js descriptors for matching.
     *
     * GET /api/face-descriptors
     */
    public function index()
    {
        $students = Student::with('user')
            ->where('face_registered', true)
            ->whereNotNull('face_encoding')
            ->get()
            ->map(function ($student) {
                // Build all available face image URLs for this student
                // Primary image (face_encoding stores the path)
                $images = [];

                $primaryPath = $student->face_encoding;
                if ($primaryPath && Storage::disk('public')->exists($primaryPath)) {
                    $images[] = Storage::url($primaryPath);
                }

                // Extra samples: left, right, blink — same naming convention from FaceRegistrationController
                $baseName = "faces/student_{$student->id}_";
                $suffixes = ['left', 'right', 'blink'];
                foreach ($suffixes as $suffix) {
                    // Find the most recent file matching this pattern
                    $files = Storage::disk('public')->files('faces');
                    foreach ($files as $file) {
                        if (str_starts_with($file, "faces/student_{$student->id}_{$suffix}_")) {
                            $images[] = Storage::url($file);
                            break; // take the first match only
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
