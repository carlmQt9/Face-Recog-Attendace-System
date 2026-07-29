@extends('layouts.app')
@section('title', 'Edit Parent')
@section('page-title', 'Edit Parent Record')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.parents.update', $parent) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <input type="text" class="form-control" value="{{ $parent->student->user->name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent / Guardian Name</label>
                        <input type="text" name="parent_name" class="form-control @error('parent_name') is-invalid @enderror"
                               value="{{ old('parent_name', $parent->parent_name) }}">
                        @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gmail Address</label>
                        <input type="email" name="gmail" class="form-control @error('gmail') is-invalid @enderror"
                               value="{{ old('gmail', $parent->gmail) }}">
                        @error('gmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Relationship</label>
                        <select name="relationship" class="form-select">
                            @foreach(['mother','father','guardian'] as $rel)
                                <option value="{{ $rel }}" {{ old('relationship', $parent->relationship) === $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
