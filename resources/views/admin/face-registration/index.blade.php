@extends('layouts.app')
@section('title', 'Face Registration')
@section('page-title', 'Face Registration')

@push('styles')
<style>
.item-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-bottom: 1px solid #f1f5f9; min-width: 0;
}
.item-row:last-child { border-bottom: none; }
.item-row .ir-icon    { flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 15px; overflow: hidden; }
.item-row .ir-info    { flex: 1; min-width: 0; }
.item-row .ir-name    { font-weight: 600; font-size: 14px; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-row .ir-sub     { font-size: 11px; color: #64748b; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; }
.item-row .ir-badge   { flex-shrink: 0; }
.item-row .ir-actions { flex-shrink: 0; display: flex; gap: 5px; align-items: center; }
</style>
@endpush

@section('content')

{{-- Info banner --}}
<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="bi bi-shield-check fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Liveness-Verified Registration</strong><br>
        <span class="small">The capture process uses <strong>blink detection</strong> and <strong>multi-angle verification</strong>
        (front, left, right, blink) to confirm a real person is present — preventing photo spoofing.
        Click <em>Capture</em> on any row to register or re-register a face.</span>
    </div>
</div>

<div class="row g-4">

    {{-- ── Students ── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-person-bounding-box me-2 text-primary"></i>Students</h6>
                <span class="badge bg-secondary">{{ $students->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($students as $student)
                <div class="item-row">
                    <div class="ir-icon" style="overflow:hidden;padding:0;">
                        @if($student->face_registered && $student->face_encoding && Storage::disk('public')->exists($student->face_encoding))
                            <img src="{{ Storage::url($student->face_encoding) }}"
                                 alt="{{ $student->user->name }}"
                                 data-lightbox="{{ Storage::url($student->face_encoding) }}"
                                 data-lightbox-caption="{{ $student->user->name }}"
                                 data-lightbox-sub="Registered Face"
                                 style="width:40px;height:40px;border-radius:10px;object-fit:cover;cursor:zoom-in;">
                        @else
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;font-weight:700;">
                                {{ strtoupper(substr($student->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="ir-info">
                        <div class="ir-name">{{ $student->user->name }}</div>
                        <div class="ir-sub">{{ $student->student_id }}</div>
                    </div>
                    <div class="ir-badge">
                        @if($student->face_registered)
                            <span class="badge bg-success" style="font-size:10px;"><i class="bi bi-shield-fill-check me-1"></i>Done</span>
                        @else
                            <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="bi bi-exclamation-circle me-1"></i>Pending</span>
                        @endif
                    </div>
                    <div class="ir-actions">
                        <a href="{{ route('admin.face-registration.capture', ['type'=>'student','id'=>$student->id]) }}"
                           class="btn btn-sm {{ $student->face_registered ? 'btn-outline-primary' : 'btn-primary' }}" title="{{ $student->face_registered ? 'Re-capture' : 'Capture' }}">
                            <i class="bi bi-camera-fill"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No students found.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Teachers ── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-person-workspace me-2 text-success"></i>Teachers</h6>
                <span class="badge bg-secondary">{{ $teachers->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($teachers as $teacher)
                <div class="item-row">
                    <div class="ir-icon" style="overflow:hidden;padding:0;">
                        @if($teacher->face_registered && $teacher->face_encoding && Storage::disk('public')->exists($teacher->face_encoding))
                            <img src="{{ Storage::url($teacher->face_encoding) }}"
                                 alt="{{ $teacher->user->name }}"
                                 data-lightbox="{{ Storage::url($teacher->face_encoding) }}"
                                 data-lightbox-caption="{{ $teacher->user->name }}"
                                 data-lightbox-sub="Registered Face — Teacher"
                                 style="width:40px;height:40px;border-radius:10px;object-fit:cover;cursor:zoom-in;">
                        @else
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;font-weight:700;">
                                {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="ir-info">
                        <div class="ir-name">{{ $teacher->user->name }}</div>
                        <div class="ir-sub">{{ $teacher->employee_id }}</div>
                    </div>
                    <div class="ir-badge">
                        @if($teacher->face_registered)
                            <span class="badge bg-success" style="font-size:10px;"><i class="bi bi-shield-fill-check me-1"></i>Done</span>
                        @else
                            <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="bi bi-exclamation-circle me-1"></i>Pending</span>
                        @endif
                    </div>
                    <div class="ir-actions">
                        <a href="{{ route('admin.face-registration.capture', ['type'=>'teacher','id'=>$teacher->id]) }}"
                           class="btn btn-sm {{ $teacher->face_registered ? 'btn-outline-success' : 'btn-success' }}" title="{{ $teacher->face_registered ? 'Re-capture' : 'Capture' }}">
                            <i class="bi bi-camera-fill"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No teachers found.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
