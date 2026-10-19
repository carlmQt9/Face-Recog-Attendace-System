<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'role'     => ['required', 'in:student,teacher,admin'],
            'student_id' => ['required_if:role,student', 'nullable', 'string', 'max:50', 'unique:students,student_id'],
            'grade_level' => ['required_if:role,student', 'nullable', 'string', 'max:50'],
            'section' => ['required_if:role,student', 'nullable', 'string', 'max:50'],
            'employee_id' => ['required_if:role,teacher', 'nullable', 'string', 'max:50', 'unique:teachers,employee_id'],
            'terms'   => ['required', 'accepted'],
        ], [
            'terms.required' => 'You must agree to the Terms and Conditions and Privacy Policy.',
            'terms.accepted' => 'You must agree to the Terms and Conditions and Privacy Policy.',
            'student_id.unique' => 'This student ID is already registered.',
            'employee_id.unique' => 'This employee ID is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.letters' => 'Password must include at least one letter.',
            'password.mixed' => 'Password must include both uppercase and lowercase letters.',
            'password.numbers' => 'Password must include at least one number.',
            'password.symbols' => 'Password must include at least one special character.',
        ]);

        // Create user - all users require approval except admins
        // Students, teachers, and other roles all need admin approval
        
        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'is_approved' => false, // All users require approval
            'approved_at' => null,
        ]);

        // Create related records based on role
        if ($validated['role'] === 'student') {
            Student::create([
                'user_id'     => $user->id,
                'student_id'  => $validated['student_id'],
                'grade_level' => $validated['grade_level'],
                'section'     => $validated['section'],
            ]);
        } elseif ($validated['role'] === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'] ?? null,
            ]);
        }

        // Return with appropriate message
        return redirect()->route('login')->with('info', 'Registration successful! Your account is pending admin approval. You will receive an email once approved.');
    }
}
