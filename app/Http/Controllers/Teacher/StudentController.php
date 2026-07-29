<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    private function teacher()
    {
        return auth()->user()->teacher;
    }

    /** List students belonging to this teacher */
    public function index()
    {
        $students = Student::with('user')
            ->where('teacher_id', $this->teacher()->id)
            ->orderBy('id')
            ->get();

        // Unassigned students (admin-created, no teacher yet) available to add
        $available = Student::with('user')
            ->whereNull('teacher_id')
            ->orderBy('id')
            ->get();

        return view('teacher.students.index', compact('students', 'available'));
    }

    /** Add a new student and assign to this teacher */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'student_id' => 'required|string|unique:students,student_id',
            'grade_level'=> 'nullable|string|max:50',
            'section'    => 'nullable|string|max:50',
            'password'   => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'student',
        ]);

        Student::create([
            'user_id'    => $user->id,
            'teacher_id' => $this->teacher()->id,
            'student_id' => $request->student_id,
            'grade_level'=> $request->grade_level,
            'section'    => $request->section,
        ]);

        return back()->with('success', "{$user->name} added to your class.");
    }

    /** Assign an existing (unassigned) student to this teacher */
    public function assign(Request $request)
    {
        $request->validate(['student_id' => 'required|exists:students,id']);

        $student = Student::findOrFail($request->student_id);

        if ($student->teacher_id !== null) {
            return back()->with('error', 'That student is already assigned to a teacher.');
        }

        $student->update(['teacher_id' => $this->teacher()->id]);

        return back()->with('success', "{$student->user->name} added to your class.");
    }

    /** Remove student from this teacher's class (does NOT delete the student) */
    public function remove(Student $student)
    {
        if ($student->teacher_id !== $this->teacher()->id) {
            abort(403);
        }

        $student->update(['teacher_id' => null]);

        return back()->with('success', "{$student->user->name} removed from your class.");
    }
}
