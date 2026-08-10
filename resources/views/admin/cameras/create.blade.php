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
                            <option value="entrance"  {{ old('type') === 'entrance'  ? 'selected' : '' }}>Entrance / Gate</option>
                            <option value="kiosk"     {{ old('type') === 'kiosk'     ? 'selected' : '' }}>Kiosk</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Local Device Camera toggle --}}
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_local_device"
                                   id="isLocalDevice" value="1"
                                   {{ old('is_local_device', true) ? 'checked' : '' }}
                                   onchange="toggleDeviceId(this.checked)">
                            <label class="form-check-label fw-semibold" for="isLocalDevice">
                                <i class="bi bi-laptop me-1 text-primary"></i> Use Local Device Camera
                                <span class="text-muted fw-normal">(laptop, phone, tablet)</span>
                            </label>
                        </div>
                        <small class="text-muted">When enabled, the teacher's device camera (built-in or phone) will be used directly via the browser — no IP address needed.</small>
                    </div>

                    <div class="mb-4" id="deviceIdField" {{ old('is_local_device', true) ? 'style=display:none' : '' }}>
                        <label class="form-label">Device Identifier <span class="text-muted">(optional)</span></label>
                        <input type="text" name="device_identifier" class="form-control"
                               value="{{ old('device_identifier') }}" placeholder="IP address or device ID">
                        <small class="text-muted">Used for IP cameras or dedicated hardware cameras.</small>
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

@push('scripts')
<script>
function toggleDeviceId(isLocal) {
    document.getElementById('deviceIdField').style.display = isLocal ? 'none' : '';
}
</script>
@endpush
@endsection
