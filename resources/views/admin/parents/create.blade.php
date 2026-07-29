@extends('layouts.app')
@section('title', 'Link Parent')
@section('page-title', 'Link Parent to Student')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.parents.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror">
                            <option value="">— Select Student —</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent / Guardian Name</label>
                        <input type="text" name="parent_name" class="form-control @error('parent_name') is-invalid @enderror"
                               value="{{ old('parent_name') }}">
                        @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gmail Address</label>
                        <input type="email" name="gmail" class="form-control @error('gmail') is-invalid @enderror"
                               value="{{ old('gmail') }}" placeholder="parent@gmail.com">
                        @error('gmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Relationship</label>
                        <select name="relationship" class="form-select">
                            <option value="mother" {{ old('relationship') === 'mother' ? 'selected' : '' }}>Mother</option>
                            <option value="father" {{ old('relationship') === 'father' ? 'selected' : '' }}>Father</option>
                            <option value="guardian" {{ old('relationship') === 'guardian' ? 'selected' : '' }}>Guardian</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Link Parent</button>
                        <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
