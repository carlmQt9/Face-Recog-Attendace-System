<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    /**
     * Show the face registration page — lists all students and teachers.
     */
    public function index()
    {
        $students = Student::with('user')->get();
        $teachers = Teacher::with('user')->get();

        return view('admin.face-registration.index', compact('students', 'teachers'));
    }

    /**
     * Show the liveness-verified webcam capture form for a specific person.
     */
    public function capture(string $type, int $id)
    {
        if ($type === 'student') {
            $person = Student::with('user')->findOrFail($id);
        } else {
            $person = Teacher::with('user')->findOrFail($id);
        }

        return view('admin.face-registration.capture', compact('person', 'type'));
    }

    /**
     * Store the captured face images (primary + extra samples) after liveness verification.
     */
    public function store(Request $request, string $type, int $id)
    {
        $request->validate([
            'face_image'        => 'required|string|min:100',  // base64 primary (front) image
            'liveness_verified' => 'nullable|in:0,1',
            'extra_samples'     => 'nullable|string',          // JSON array of base64 images
        ]);

        $faceImage = $request->input('face_image');

        // Guard: must be a non-empty base64 string
        if (empty($faceImage) || strlen($faceImage) < 100) {
            return back()->withErrors(['face_image' => 'No face image was captured. Please complete the verification steps.']);
        }

        // ── Decode and store primary face image ───────────────────────────────
        $primaryPath = $this->saveBase64Image(
            $faceImage,
            "faces/{$type}_{$id}_primary_" . time() . '.jpg'
        );

        // ── Store extra samples (left, right, blink) ──────────────────────────
        $extraPaths = [];
        if ($request->filled('extra_samples')) {
            $extras = json_decode($request->input('extra_samples'), true);
            if (is_array($extras)) {
                $labels = ['left', 'right', 'blink'];
                foreach ($extras as $i => $b64) {
                    if (empty($b64) || strlen($b64) < 100) continue; // skip blank slots
                    $label        = $labels[$i] ?? "sample_{$i}";
                    $extraPaths[] = $this->saveBase64Image(
                        $b64,
                        "faces/{$type}_{$id}_{$label}_" . time() . '.jpg'
                    );
                }
            }
        }

        // ── Update the model record ───────────────────────────────────────────
        if ($type === 'student') {
            $person = Student::findOrFail($id);
        } else {
            $person = Teacher::findOrFail($id);
        }

        $person->face_encoding   = $primaryPath;
        $person->face_registered = true;
        $person->save();

        $livenessVerified = $request->input('liveness_verified') === '1';
        $sampleCount      = 1 + count($extraPaths);
        $msg = $livenessVerified
            ? "{$person->user->name}'s face registered with liveness verification ({$sampleCount} samples captured)."
            : "{$person->user->name}'s face registered successfully (1 sample captured).";

        return redirect()->route('admin.face-registration.index')->with('success', $msg);
    }

    /**
     * Decode a base64 image string and persist it to public storage.
     * Returns the storage path, or throws if the data is invalid.
     */
    private function saveBase64Image(string $base64, string $path): string
    {
        // Strip data URI prefix (data:image/jpeg;base64, or data:image/png;base64,)
        $data = preg_replace('/^data:image\/[a-zA-Z+]+;base64,/', '', $base64);
        $data = str_replace([' ', "\n", "\r"], ['+', '', ''], $data);

        $decoded = base64_decode($data, strict: true);
        if ($decoded === false) {
            throw new \RuntimeException("Invalid base64 image data for path: {$path}");
        }

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
