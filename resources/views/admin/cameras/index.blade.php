@extends('layouts.app')
@section('title', 'Camera Management')
@section('page-title', 'Camera Management')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCameraModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Add Camera
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th class="d-none d-sm-table-cell">Location</th>
                    <th class="d-none d-md-table-cell">Type</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cameras as $camera)
                    <tr>
                        <td>
                            {{ $camera->name }}
                            <div class="text-muted d-sm-none" style="font-size:11px;">{{ $camera->location }}</div>
                        </td>
                        <td class="d-none d-sm-table-cell">{{ $camera->location }}</td>
                        <td class="d-none d-md-table-cell"><span class="badge bg-secondary">{{ ucfirst($camera->type) }}</span></td>
                        <td>
                            @if($camera->is_local_device)
                                <span class="badge bg-primary"><i class="bi bi-laptop me-1"></i>Local</span>
                            @else
                                <span class="badge bg-info text-dark"><i class="bi bi-hdd-network me-1"></i>IP</span>
                            @endif
                        </td>
                        <td>
                            @if($camera->is_active)
                                <span class="badge bg-success"><i class="bi bi-circle-fill me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                            <form action="{{ route('admin.cameras.toggle', $camera) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-{{ $camera->is_active ? 'warning' : 'success' }}">
                                    {{ $camera->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.cameras.edit', $camera) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.cameras.destroy', $camera) }}" method="POST" class="d-inline"
                                  onsubmit="return false"
                                  data-confirm-title="Delete Camera"
                                  data-confirm-message="Are you sure you want to delete &quot;{{ $camera->name }}&quot;? This cannot be undone."
                                  data-confirm-ok="Delete"
                                  data-confirm-type="danger"
                                  data-confirm-icon="🗑️">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No cameras added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
{{ $cameras->links() }}

{{-- ── Add Camera Modal ── --}}
<div class="modal fade" id="addCameraModal" tabindex="-1" aria-labelledby="addCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0891b2,#4f46e5);border:none;">
                <h5 class="modal-title text-white fw-bold" id="addCameraModalLabel">
                    <i class="bi bi-camera-video-fill me-2"></i>Add Camera
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('admin.cameras.store') }}" method="POST" id="addCameraForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Camera Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Classroom 101 Camera">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location"
                               class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location') }}" placeholder="e.g. Room 101 or Main Gate">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="classroom" {{ old('type') === 'classroom' ? 'selected' : '' }}>Classroom</option>
                            <option value="entrance"  {{ old('type') === 'entrance'  ? 'selected' : '' }}>Entrance / Gate</option>
                            <option value="kiosk"     {{ old('type') === 'kiosk'     ? 'selected' : '' }}>Kiosk</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_local_device"
                                   id="modalIsLocalDevice" value="1"
                                   {{ old('is_local_device') ? 'checked' : '' }}
                                   onchange="toggleModalDeviceId(this.checked)">
                            <label class="form-check-label fw-semibold" for="modalIsLocalDevice">
                                <i class="bi bi-laptop me-1 text-primary"></i> Use Local Device Camera
                                <span class="text-muted fw-normal">(laptop, phone, tablet)</span>
                            </label>
                        </div>
                        <small class="text-muted">When enabled, the teacher's browser camera is used — no IP address needed.</small>
                    </div>
                    <div id="modalDeviceIdField" style="{{ old('is_local_device') ? 'display:none' : '' }}">
                        <label class="form-label fw-semibold">Device Identifier <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="device_identifier" class="form-control"
                               value="{{ old('device_identifier') }}" placeholder="IP address or device ID">
                        <small class="text-muted">Used for IP cameras or dedicated hardware cameras.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:14px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addCameraForm" class="btn btn-primary px-4">
                    <i class="bi bi-camera-video-fill me-1"></i> Save Camera
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleModalDeviceId(isLocal) {
    document.getElementById('modalDeviceIdField').style.display = isLocal ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
        new bootstrap.Modal(document.getElementById('addCameraModal')).show();
    @endif
});
</script>
@endpush
