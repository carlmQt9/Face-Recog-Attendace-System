<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('student')->orderBy('role')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => 'required|in:admin,teacher,student',
            'employee_id' => 'required_if:role,teacher|nullable|string|unique:teachers,employee_id',
            'student_id'  => 'required_if:role,student|nullable|string|unique:students,student_id',
            'grade_level' => 'nullable|string|max:50',
            'section'     => 'nullable|string|max:50',
            'department'  => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        // Create role-specific profile
        if ($request->role === 'student') {
            Student::create([
                'user_id'     => $user->id,
                'student_id'  => $request->student_id,
                'grade_level' => $request->grade_level,
                'section'     => $request->section,
            ]);
        } elseif ($request->role === 'teacher') {
            Teacher::create([
                'user_id'     => $user->id,
                'employee_id' => $request->employee_id,
                'department'  => $request->department,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} account created successfully.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User removed.');
    }
}
