<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

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
            'face_image'        => 'required|string|min:100',
            'liveness_verified' => 'nullable|in:0,1',
            'extra_samples'     => 'nullable|string',
        ]);

        $faceImage = $request->input('face_image');

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
                    if (empty($b64) || strlen($b64) < 100) continue;
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
     * Decode a base64 image string and save it directly into public/faces/.
     * Saves to public_path($path) so the file is accessible via asset($path)
     * on ANY host — no storage symlink required (works on localhost AND InfinityFree).
     *
     * Returns the relative path (e.g. "faces/student_1_primary_1234.jpg") which
     * is stored in the DB and resolved to a URL by AppServiceProvider::faceImageUrl().
     */
    private function saveBase64Image(string $base64, string $path): string
    {
        $data = preg_replace('/^data:image\/[a-zA-Z+]+;base64,/', '', $base64);
        $data = str_replace([' ', "\n", "\r"], ['+', '', ''], $data);

        $decoded = base64_decode($data, strict: true);
        if ($decoded === false) {
            throw new \RuntimeException("Invalid base64 image data for path: {$path}");
        }

        $fullPath = public_path($path);
        $dir      = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $decoded);

        return trim($path);
    }
}
