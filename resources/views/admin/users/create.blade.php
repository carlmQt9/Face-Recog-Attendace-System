@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST" id="userForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror"
                                    onchange="toggleFields()">
                                <option value="">— Select Role —</option>
                                <option value="admin"   {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Teacher fields --}}
                        <div id="teacherFields" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror"
                                           value="{{ old('employee_id') }}">
                                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control" value="{{ old('department') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Student fields --}}
                        <div id="studentFields" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror"
                                           value="{{ old('student_id') }}">
                                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Grade Level</label>
                                    <input type="text" name="grade_level" class="form-control" value="{{ old('grade_level') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Section</label>
                                    <input type="text" name="section" class="form-control" value="{{ old('section') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('teacherFields').classList.toggle('d-none', role !== 'teacher');
    document.getElementById('studentFields').classList.toggle('d-none', role !== 'student');
}
// Run on load in case of old() values
toggleFields();
</script>
@endpush
