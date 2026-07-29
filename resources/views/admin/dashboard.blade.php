@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#1a73e8,#0d47a1)">
            <div class="fs-2 fw-bold">{{ $stats['total_students'] }}</div>
            <div class="small opacity-75">Total Students</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#34a853,#1b5e20)">
            <div class="fs-2 fw-bold">{{ $stats['total_teachers'] }}</div>
            <div class="small opacity-75">Total Teachers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#fbbc04,#e65100)">
            <div class="fs-2 fw-bold">{{ $stats['active_cameras'] }} / {{ $stats['total_cameras'] }}</div>
            <div class="small opacity-75">Active Cameras</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#ea4335,#880e4f)">
            <div class="fs-2 fw-bold">{{ $stats['today_attendance'] }}</div>
            <div class="small opacity-75">Present Today</div>
        </div>
    </div>
</div>

{{-- Recent Attendance --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Attendance Events</h6>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Camera / Location</th>
                        <th>Method</th>
                        <th>Time</th>
                        <th>Notified</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendance as $record)
                        <tr>
                            <td>{{ $record->student->user->name }}</td>
                            <td>{{ $record->camera->location }}</td>
                            <td>
                                <span class="badge {{ $record->method === 'manual' ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->method)) }}
                                </span>
                            </td>
                            <td>{{ $record->arrived_at->format('h:i A') }}</td>
                            <td>
                                @if($record->notification_sent)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-secondary"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No attendance records today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
