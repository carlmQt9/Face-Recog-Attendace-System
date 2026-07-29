@extends('layouts.app')
@section('title', 'Face Registration')
@section('page-title', 'Face Registration')

@section('content')
<div class="row g-4">
    {{-- Students --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-bounding-box me-2 text-primary"></i>Students</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>ID</th><th>Face</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->user->name }}</td>
                                <td>{{ $student->student_id }}</td>
                                <td>
                                    @if($student->face_registered)
                                        <span class="badge bg-success">Registered</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.face-registration.capture', ['type'=>'student','id'=>$student->id]) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-camera-fill"></i> Capture
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Teachers --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-workspace me-2 text-success"></i>Teachers</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Employee ID</th><th>Face</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->user->name }}</td>
                                <td>{{ $teacher->employee_id }}</td>
                                <td>
                                    @if($teacher->face_registered)
                                        <span class="badge bg-success">Registered</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.face-registration.capture', ['type'=>'teacher','id'=>$teacher->id]) }}"
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-camera-fill"></i> Capture
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
