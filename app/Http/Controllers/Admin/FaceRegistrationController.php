<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->get();
        $teachers = Teacher::with('user')->get();

        return view('admin.face-registration.index', compact('students', 'teachers'));
    }

    public function capture(string $type, int $id)
    {
        if ($type === 'student') {
            $person = Student::with('user')->findOrFail($id);
        } else {
            $person = Teacher::with('user')->findOrFail($id);
        }

        return view('admin.face-registration.capture', compact('person', 'type'));
    }

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

        // Save primary face image directly into public/storage/face-photos/
        $primaryPath = $this->saveBase64Image(
            $faceImage,
            "face-photos/{$type}_{$id}_primary_" . time() . '.jpg'
        );

        // Save extra samples (left, right, blink)
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
                        "face-photos/{$type}_{$id}_{$label}_" . time() . '.jpg'
                    );
                }
            }
        }

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
     * Save a base64 image directly into public/storage/{path}.
     *
     * Writes directly with file_put_contents — no Storage facade, no disk config needed.
     * Files land in: public/storage/face-photos/student_1_primary_xxx.jpg
     * Served as: https://yourdomain.com/storage/face-photos/...
     *
     * Works on InfinityFree without any special configuration.
     */
    private function saveBase64Image(string $base64, string $path): string
    {
        $data    = preg_replace('/^data:image\/[a-zA-Z+]+;base64,/', '', $base64);
        $data    = str_replace([' ', "\n", "\r"], ['+', '', ''], $data);
        $decoded = base64_decode($data, strict: true);

        if ($decoded === false) {
            throw new \RuntimeException("Invalid base64 image data for path: {$path}");
        }

        // Write directly into public/storage/ — no Storage facade, no disk config
        $fullPath = public_path('storage/' . $path);
        $dir      = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $decoded);

        return $path;
    }
}
