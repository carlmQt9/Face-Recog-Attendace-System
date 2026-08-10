@extends('layouts.app')
@section('title', 'Parent Setup')
@section('page-title', 'Parent / Guardian Setup')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#linkParentModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Link Parent
    </button>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- ── Desktop table ── --}}
        <div class="desk-list table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Parent Name</th>
                        <th>Gmail</th>
                        <th>Relationship</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                    <tr>
                        <td class="align-middle fw-semibold">{{ $parent->student->user->name }}</td>
                        <td class="align-middle">{{ $parent->parent_name }}</td>
                        <td class="align-middle">{{ $parent->gmail }}</td>
                        <td class="align-middle">
                            <span class="badge bg-secondary">{{ ucfirst($parent->relationship) }}</span>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex gap-1 justify-content-end" style="white-space:nowrap;">
                                <button class="btn btn-sm btn-outline-primary" title="Edit"
                                        onclick="openEditParentModal(
                                            {{ $parent->id }},
                                            '{{ addslashes($parent->parent_name) }}',
                                            '{{ addslashes($parent->gmail) }}',
                                            '{{ $parent->relationship }}',
                                            {{ $parent->student_id }},
                                            '{{ addslashes($parent->student->user->name) }}'
                                        )">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline"
                                      onsubmit="return false"
                                      data-confirm-title="Remove Parent Record"
                                      data-confirm-message="Remove {{ $parent->parent_name }}? The student will lose their parent contact."
                                      data-confirm-ok="Remove" data-confirm-type="danger" data-confirm-icon="👨‍👩‍👧">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No parent records yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Mobile card rows ── --}}
        <div class="mob-list">
            @forelse($parents as $parent)
            <div class="item-row">
                <div class="ir-icon" style="background:rgba(5,150,105,.1);color:#059669;">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <div class="ir-info">
                    <div class="ir-name">{{ $parent->parent_name }}</div>
                    <div class="ir-sub">
                        <span class="fw-semibold text-dark">{{ $parent->student->user->name }}</span>
                        &middot; {{ $parent->gmail }}
                        &middot; {{ ucfirst($parent->relationship) }}
                    </div>
                </div>
                <div class="ir-actions">
                    <button class="btn btn-sm btn-outline-primary" title="Edit"
                            onclick="openEditParentModal(
                                {{ $parent->id }},
                                '{{ addslashes($parent->parent_name) }}',
                                '{{ addslashes($parent->gmail) }}',
                                '{{ $parent->relationship }}',
                                {{ $parent->student_id }},
                                '{{ addslashes($parent->student->user->name) }}'
                            )">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline"
                          onsubmit="return false"
                          data-confirm-title="Remove Parent Record"
                          data-confirm-message="Remove {{ $parent->parent_name }}? The student will lose their parent contact."
                          data-confirm-ok="Remove" data-confirm-type="danger" data-confirm-icon="👨‍👩‍👧">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Remove">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">No parent records yet.</div>
            @endforelse
        </div>

    </div>
</div>
{{ $parents->links() }}

{{-- ── Link Parent Modal ── --}}
<div class="modal fade" id="linkParentModal" tabindex="-1" aria-labelledby="linkParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;max-height:90vh;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a6b3c,#0c3d8a);border:none;">
                <h5 class="modal-title text-white fw-bold" id="linkParentModalLabel">
                    <i class="bi bi-heart-fill me-2"></i>Link Parent / Guardian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible py-2 small mb-3 auto-dismiss">
                        <strong>Please fix:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" style="font-size:10px;"></button>
                    </div>
                @endif
                <form action="{{ route('admin.parents.store') }}" method="POST" id="linkParentForm" data-validate="true">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" data-label="Student" class="form-select @error('student_id') is-invalid @enderror">
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
                        <label class="form-label fw-semibold">Parent / Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" name="parent_name" data-label="Parent Name"
                               class="form-control @error('parent_name') is-invalid @enderror"
                               value="{{ old('parent_name') }}" placeholder="e.g. Maria Cruz">
                        @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gmail Address <span class="text-danger">*</span></label>
                        <input type="email" name="gmail" data-label="Gmail"
                               class="form-control @error('gmail') is-invalid @enderror"
                               value="{{ old('gmail') }}" placeholder="parent@gmail.com">
                        @error('gmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Relationship <span class="text-danger">*</span></label>
                        <select name="relationship" class="form-select">
                            <option value="mother"   {{ old('relationship') === 'mother'   ? 'selected' : '' }}>Mother</option>
                            <option value="father"   {{ old('relationship') === 'father'   ? 'selected' : '' }}>Father</option>
                            <option value="guardian" {{ old('relationship') === 'guardian' ? 'selected' : '' }}>Guardian</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="linkParentForm" class="btn btn-success px-4">
                    <i class="bi bi-heart-fill me-1"></i> Link Parent
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Parent Modal ── --}}
<div class="modal fade" id="editParentModal" tabindex="-1" aria-labelledby="editParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;max-height:90vh;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a6b3c,#0c3d8a);border:none;">
                <h5 class="modal-title text-white fw-bold" id="editParentModalLabel">
                    <i class="bi bi-pencil-fill me-2"></i>Edit Parent Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editParentForm" method="POST" data-validate="true">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student</label>
                        <input type="text" id="editParentStudentName" class="form-control bg-light" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent / Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" name="parent_name" id="editParentName" data-label="Parent Name" class="form-control" required placeholder="e.g. Maria Cruz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gmail Address <span class="text-danger">*</span></label>
                        <input type="email" name="gmail" id="editParentGmail" data-label="Gmail" class="form-control" required placeholder="parent@gmail.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Relationship <span class="text-danger">*</span></label>
                        <select name="relationship" id="editParentRelationship" class="form-select">
                            <option value="mother">Mother</option>
                            <option value="father">Father</option>
                            <option value="guardian">Guardian</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editParentForm" class="btn btn-success px-4">
                    <i class="bi bi-floppy-fill me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditParentModal(id, parentName, gmail, relationship, studentId, studentName) {
    const form = document.getElementById('editParentForm');
    form.action = `/admin/parents/${id}`;
    document.getElementById('editParentStudentName').value  = studentName;
    document.getElementById('editParentName').value         = parentName;
    document.getElementById('editParentGmail').value        = gmail;
    document.getElementById('editParentRelationship').value = relationship;
    new bootstrap.Modal(document.getElementById('editParentModal')).show();
}
document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
        new bootstrap.Modal(document.getElementById('linkParentModal')).show();
    @endif
});
</script>
@endpush
