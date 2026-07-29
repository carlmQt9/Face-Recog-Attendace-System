@extends('layouts.app')
@section('title', 'Attendance Archive')
@section('page-title', 'Attendance Archive')

@section('content')
{{-- Filter Bar --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select">
                    <option value="">All Students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Camera</label>
                <select name="camera_id" class="form-select">
                    <option value="">All Cameras</option>
                    @foreach($cameras as $c)
                        <option value="{{ $c->id }}" {{ request('camera_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">Reset</a>
                <a href="{{ route('admin.attendance.export', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-download me-1"></i> CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Location</th>
                    <th>Method</th>
                    <th>Result</th>
                    <th>Arrived At</th>
                    <th>Notified</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->student->user->name }}</td>
                        <td>{{ $record->student->student_id }}</td>
                        <td>{{ $record->camera->location }}</td>
                        <td>
                            <span class="badge {{ $record->method === 'manual' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                {{ ucfirst(str_replace('_', ' ', $record->method)) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $record->scan_result === 'success' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($record->scan_result) }}
                            </span>
                        </td>
                        <td>{{ $record->arrived_at->format('M d, Y h:i A') }}</td>
                        <td>
                            @if($record->notification_sent)
                                <i class="bi bi-envelope-check-fill text-success"></i>
                            @else
                                <i class="bi bi-envelope-x text-secondary"></i>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $records->links() }}
@endsection
