<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentContact;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index()
    {
        $parents = ParentContact::with('student.user')->paginate(20);
        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        $students = Student::with('user')->whereDoesntHave('parent')->get();
        return view('admin.parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'   => 'required|exists:students,id|unique:parents,student_id',
            'parent_name'  => 'required|string|max:255',
            'gmail'        => 'required|email|unique:parents,gmail',
            'relationship' => 'required|in:mother,father,guardian',
        ]);

        ParentContact::create($request->only('student_id', 'parent_name', 'gmail', 'relationship'));

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent linked successfully.');
    }

    public function edit(ParentContact $parent)
    {
        $students = Student::with('user')->get();
        return view('admin.parents.edit', compact('parent', 'students'));
    }

    public function update(Request $request, ParentContact $parent)
    {
        $request->validate([
            'parent_name'  => 'required|string|max:255',
            'gmail'        => 'required|email|unique:parents,gmail,' . $parent->id,
            'relationship' => 'required|in:mother,father,guardian',
        ]);

        $parent->update($request->only('parent_name', 'gmail', 'relationship'));

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent details updated.');
    }

    public function destroy(ParentContact $parent)
    {
        $parent->delete();
        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent record removed.');
    }
}
