<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
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
     * Show the webcam capture form for a specific person.
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
     * Store the captured face image and encoding path.
     * In a real system this would call a Python face-recognition service.
     */
    public function store(Request $request, string $type, int $id)
    {
        $request->validate([
            'face_image' => 'required|string', // base64 encoded image from webcam
        ]);

        // Decode and store the base64 webcam snapshot
        $imageData = $request->input('face_image');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decoded   = base64_decode($imageData);

        $filename = "faces/{$type}_{$id}_" . time() . '.png';
        Storage::disk('public')->put($filename, $decoded);

        // Update the record
        if ($type === 'student') {
            $person = Student::findOrFail($id);
        } else {
            $person = Teacher::findOrFail($id);
        }

        $person->face_encoding  = $filename;
        $person->face_registered = true;
        $person->save();

        return redirect()->route('admin.face-registration.index')
            ->with('success', "{$person->user->name}'s face has been registered successfully.");
    }
}
