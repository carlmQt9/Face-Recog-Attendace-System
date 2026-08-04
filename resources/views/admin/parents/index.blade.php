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
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Parent Name</th>
                    <th class="d-none d-sm-table-cell">Gmail</th>
                    <th class="d-none d-md-table-cell">Relationship</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parents as $parent)
                    <tr>
                        <td>{{ $parent->student->user->name }}</td>
                        <td>
                            {{ $parent->parent_name }}
                            <div class="text-muted d-sm-none" style="font-size:11px;">{{ $parent->gmail }}</div>
                        </td>
                        <td class="d-none d-sm-table-cell">{{ $parent->gmail }}</td>
                        <td class="d-none d-md-table-cell"><span class="badge bg-secondary">{{ ucfirst($parent->relationship) }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline"
                                  onsubmit="return false"
                                  data-confirm-title="Remove Parent Record"
                                  data-confirm-message="Are you sure you want to remove {{ $parent->parent_name }}? The linked student will lose their parent contact."
                                  data-confirm-ok="Remove" data-confirm-type="danger" data-confirm-icon="👨‍👩‍👧">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No parent records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
{{ $parents->links() }}

{{-- ── Link Parent Modal ── --}}
<div class="modal fade" id="linkParentModal" tabindex="-1" aria-labelledby="linkParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669,#06b6d4);border:none;">
                <h5 class="modal-title text-white fw-bold" id="linkParentModalLabel">
                    <i class="bi bi-heart-fill me-2"></i>Link Parent / Guardian
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
                <form action="{{ route('admin.parents.store') }}" method="POST" id="linkParentForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-semibold">Parent / Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" name="parent_name"
                               class="form-control @error('parent_name') is-invalid @enderror"
                               value="{{ old('parent_name') }}" placeholder="e.g. Maria Cruz">
                        @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gmail Address <span class="text-danger">*</span></label>
                        <input type="email" name="gmail"
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
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:14px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="linkParentForm" class="btn btn-success px-4">
                    <i class="bi bi-heart-fill me-1"></i> Link Parent
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
        new bootstrap.Modal(document.getElementById('linkParentModal')).show();
    @endif
});
</script>
@endpush
