@extends('layouts.app')
@section('title', 'Edit Camera')
@section('page-title', 'Edit Camera')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.cameras.update', $camera) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Camera Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $camera->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $camera->location) }}">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach(['classroom','entrance','kiosk'] as $t)
                                <option value="{{ $t }}" {{ old('type', $camera->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Local Device Camera toggle --}}
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_local_device"
                                   id="isLocalDevice" value="1"
                                   {{ old('is_local_device', $camera->is_local_device) ? 'checked' : '' }}
                                   onchange="toggleDeviceId(this.checked)">
                            <label class="form-check-label fw-semibold" for="isLocalDevice">
                                <i class="bi bi-laptop me-1 text-primary"></i> Use Local Device Camera
                                <span class="text-muted fw-normal">(laptop, phone, tablet)</span>
                            </label>
                        </div>
                        <small class="text-muted">When enabled, the teacher's device camera will be used directly via the browser.</small>
                    </div>

                    <div class="mb-4" id="deviceIdField"
                         style="{{ old('is_local_device', $camera->is_local_device) ? 'display:none' : '' }}">
                        <label class="form-label">Device Identifier <span class="text-muted">(optional)</span></label>
                        <input type="text" name="device_identifier" class="form-control"
                               value="{{ old('device_identifier', $camera->device_identifier) }}"
                               placeholder="IP address or device ID">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Camera</button>
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
