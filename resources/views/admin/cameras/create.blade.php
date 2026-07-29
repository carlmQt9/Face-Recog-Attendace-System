@extends('layouts.app')
@section('title', 'Add Camera')
@section('page-title', 'Add Camera')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.cameras.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Camera Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Classroom 101 Camera">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location') }}" placeholder="e.g. Room 101 or Main Gate">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="classroom" {{ old('type') === 'classroom' ? 'selected' : '' }}>Classroom</option>
                            <option value="entrance" {{ old('type') === 'entrance' ? 'selected' : '' }}>Entrance / Gate</option>
                            <option value="kiosk" {{ old('type') === 'kiosk' ? 'selected' : '' }}>Kiosk</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Device Identifier <span class="text-muted">(optional)</span></label>
                        <input type="text" name="device_identifier" class="form-control"
                               value="{{ old('device_identifier') }}" placeholder="IP address or device ID">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Camera</button>
                        <a href="{{ route('admin.cameras.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
