@extends('layouts.app')
@section('title', 'Camera Management')
@section('page-title', 'Camera Management')

@push('styles')
<style>
.item-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-bottom: 1px solid #f1f5f9; min-width: 0;
}
.item-row:last-child { border-bottom: none; }
.item-row .ir-icon  { flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 18px; }
.item-row .ir-info  { flex: 1; min-width: 0; }
.item-row .ir-name  { font-weight: 600; font-size: 14px; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-row .ir-sub   { font-size: 11px; color: #64748b; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; }
.item-row .ir-badges { flex-shrink: 0; display: flex; gap: 4px; flex-wrap: wrap; justify-content: flex-end; }
.item-row .ir-actions { flex-shrink: 0; display: flex; gap: 5px; align-items: center; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCameraModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Add Camera
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse($cameras as $camera)
        <div class="item-row">
            <div class="ir-icon" style="background:{{ $camera->is_local_device ? 'rgba(79,70,229,.1)' : 'rgba(6,182,212,.1)' }}; color:{{ $camera->is_local_device ? '#4f46e5' : '#0891b2' }};">
                <i class="bi bi-{{ $camera->is_local_device ? 'laptop' : 'camera-video-fill' }}"></i>
            </div>
            <div class="ir-info">
                <div class="ir-name">{{ $camera->name }}</div>
                <div class="ir-sub">{{ $camera->location }} &middot; {{ ucfirst($camera->type) }}</div>
            </div>
            <div class="ir-badges">
                @if($camera->is_local_device)
                    <span class="badge bg-primary" style="font-size:10px;"><i class="bi bi-laptop me-1"></i>Local</span>
                @else
                    <span class="badge bg-info text-dark" style="font-size:10px;"><i class="bi bi-hdd-network me-1"></i>IP</span>
                @endif
                @if($camera->is_active)
                    <span class="badge bg-success" style="font-size:10px;">Active</span>
                @else
                    <span class="badge bg-secondary" style="font-size:10px;">Inactive</span>
                @endif
            </div>
            <div class="ir-actions">
                <form action="{{ route('admin.cameras.toggle', $camera) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-{{ $camera->is_active ? 'warning' : 'success' }}" title="{{ $camera->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="bi bi-{{ $camera->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                    </button>
                </form>
                <button class="btn btn-sm btn-outline-primary" title="Edit"
                        onclick="openEditCameraModal(
                            {{ $camera->id }},
                            '{{ addslashes($camera->name) }}',
                            '{{ addslashes($camera->location) }}',
                            '{{ $camera->type }}',
                            {{ $camera->is_local_device ? 'true' : 'false' }},
                            '{{ addslashes($camera->device_identifier ?? '') }}'
                        )">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <form action="{{ route('admin.cameras.destroy', $camera) }}" method="POST" class="d-inline"
                      onsubmit="return false"
                      data-confirm-title="Delete Camera"
                      data-confirm-message="Delete &quot;{{ $camera->name }}&quot;? This cannot be undone."
                      data-confirm-ok="Delete" data-confirm-type="danger" data-confirm-icon="🗑️">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">No cameras added yet.</div>
        @endforelse
    </div>
</div>
{{ $cameras->links() }}

{{-- ── Add Camera Modal ── --}}
<div class="modal fade" id="addCameraModal" tabindex="-1" aria-labelledby="addCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
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
                        <strong>Please fix:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('admin.cameras.store') }}" method="POST" id="addCameraForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Camera Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Classroom 101 Camera">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
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
                        <small class="text-muted">Browser camera — no IP address needed.</small>
                    </div>
                    <div id="modalDeviceIdField" style="{{ old('is_local_device') ? 'display:none' : '' }}">
                        <label class="form-label fw-semibold">Device Identifier <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="device_identifier" class="form-control"
                               value="{{ old('device_identifier') }}" placeholder="IP address or device ID">
                        <small class="text-muted">For IP or dedicated cameras.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addCameraForm" class="btn btn-primary px-4">
                    <i class="bi bi-camera-video-fill me-1"></i> Save Camera
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Camera Modal ── --}}
<div class="modal fade" id="editCameraModal" tabindex="-1" aria-labelledby="editCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0891b2,#4f46e5);border:none;">
                <h5 class="modal-title text-white fw-bold" id="editCameraModalLabel">
                    <i class="bi bi-pencil-fill me-2"></i>Edit Camera
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editCameraForm" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Camera Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCameraName" class="form-control" required placeholder="e.g. Classroom 101 Camera">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" id="editCameraLocation" class="form-control" required placeholder="e.g. Room 101">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" id="editCameraType" class="form-select">
                            <option value="classroom">Classroom</option>
                            <option value="entrance">Entrance / Gate</option>
                            <option value="kiosk">Kiosk</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_local_device"
                                   id="editCameraIsLocal" value="1"
                                   onchange="toggleEditDeviceId(this.checked)">
                            <label class="form-check-label fw-semibold" for="editCameraIsLocal">
                                <i class="bi bi-laptop me-1 text-primary"></i> Use Local Device Camera
                                <span class="text-muted fw-normal">(laptop, phone, tablet)</span>
                            </label>
                        </div>
                        <small class="text-muted">Browser camera — no IP address needed.</small>
                    </div>
                    <div id="editDeviceIdField">
                        <label class="form-label fw-semibold">Device Identifier <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" name="device_identifier" id="editCameraDeviceId" class="form-control" placeholder="IP address or device ID">
                        <small class="text-muted">For IP or dedicated cameras.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editCameraForm" class="btn btn-primary px-4">
                    <i class="bi bi-floppy-fill me-1"></i> Save Changes
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
function toggleEditDeviceId(isLocal) {
    document.getElementById('editDeviceIdField').style.display = isLocal ? 'none' : '';
}
function openEditCameraModal(id, name, location, type, isLocal, deviceId) {
    const form = document.getElementById('editCameraForm');
    form.action = `/admin/cameras/${id}`;
    document.getElementById('editCameraName').value     = name;
    document.getElementById('editCameraLocation').value = location;
    document.getElementById('editCameraType').value     = type;
    document.getElementById('editCameraIsLocal').checked = isLocal;
    document.getElementById('editCameraDeviceId').value = deviceId;
    toggleEditDeviceId(isLocal);
    new bootstrap.Modal(document.getElementById('editCameraModal')).show();
}
document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
        new bootstrap.Modal(document.getElementById('addCameraModal')).show();
    @endif
});
</script>
@endpush
