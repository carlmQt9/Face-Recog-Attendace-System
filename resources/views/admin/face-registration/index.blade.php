@extends('layouts.app')
@section('title', 'Face Registration')
@section('page-title', 'Face Registration')

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
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Student ID</th>
                            <th>Face Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="fw-semibold">{{ $student->user->name }}</td>
                                <td class="text-muted">{{ $student->student_id }}</td>
                                <td>
                                    @if($student->face_registered)
                                        <span class="badge bg-success">
                                            <i class="bi bi-shield-fill-check me-1"></i>Registered
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-circle me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.face-registration.capture', ['type'=>'student','id'=>$student->id]) }}"
                                       class="btn btn-sm {{ $student->face_registered ? 'btn-outline-primary' : 'btn-primary' }}">
                                        <i class="bi bi-camera-fill me-1"></i>
                                        {{ $student->face_registered ? 'Re-capture' : 'Capture' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No students found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Face Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                            <tr>
                                <td class="fw-semibold">{{ $teacher->user->name }}</td>
                                <td class="text-muted">{{ $teacher->employee_id }}</td>
                                <td>
                                    @if($teacher->face_registered)
                                        <span class="badge bg-success">
                                            <i class="bi bi-shield-fill-check me-1"></i>Registered
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-circle me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.face-registration.capture', ['type'=>'teacher','id'=>$teacher->id]) }}"
                                       class="btn btn-sm {{ $teacher->face_registered ? 'btn-outline-success' : 'btn-success' }}">
                                        <i class="bi bi-camera-fill me-1"></i>
                                        {{ $teacher->face_registered ? 'Re-capture' : 'Capture' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No teachers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
