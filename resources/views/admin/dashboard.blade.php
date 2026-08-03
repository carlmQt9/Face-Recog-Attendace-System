@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a73e8,#0d47a1)">
            <div class="fs-2 fw-bold" id="stat-students">{{ $stats['total_students'] }}</div>
            <div class="small opacity-75">Total Students</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#34a853,#1b5e20)">
            <div class="fs-2 fw-bold" id="stat-teachers">{{ $stats['total_teachers'] }}</div>
            <div class="small opacity-75">Total Teachers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#fbbc04,#e65100)">
            <div class="fs-2 fw-bold" id="stat-cameras">{{ $stats['active_cameras'] }} / {{ $stats['total_cameras'] }}</div>
            <div class="small opacity-75">Active Cameras</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#ea4335,#880e4f)">
            <div class="fs-2 fw-bold" id="stat-attendance">{{ $stats['today_attendance'] }}</div>
            <div class="small opacity-75">Present Today</div>
        </div>
    </div>
</div>

{{-- Recent Attendance --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-activity me-2"></i>Recent Attendance Events
            <span id="liveIndicator" class="ms-2 badge bg-success" style="font-size:10px;vertical-align:middle;">● Live</span>
        </h6>
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
                <tbody id="recentTableBody">
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
                        <tr id="emptyRow"><td colspan="5" class="text-center text-muted py-4">No attendance records today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const STATS_URL = '{{ route('admin.dashboard.stats') }}';
let lastTopName = '{{ $recentAttendance->first()?->student->user->name ?? '' }}';

async function pollAdminDashboard() {
    try {
        const resp = await fetch(STATS_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (!resp.ok) return;
        const data = await resp.json();

        // Update stat cards
        document.getElementById('stat-students').textContent   = data.stats.total_students;
        document.getElementById('stat-teachers').textContent   = data.stats.total_teachers;
        document.getElementById('stat-cameras').textContent    = data.stats.active_cameras + ' / ' + data.stats.total_cameras;
        document.getElementById('stat-attendance').textContent = data.stats.today_attendance;

        // Update recent table only if there's new data
        if (data.recent.length > 0 && data.recent[0].name !== lastTopName) {
            lastTopName = data.recent[0].name;
            const tbody = document.getElementById('recentTableBody');
            tbody.innerHTML = data.recent.map(r => `
                <tr>
                    <td>${r.name}</td>
                    <td>${r.camera}</td>
                    <td><span class="badge ${r.method === 'Manual' ? 'bg-warning text-dark' : 'bg-success'}">${r.method}</span></td>
                    <td>${r.time}</td>
                    <td>${r.notified ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-secondary"></i>'}</td>
                </tr>`).join('');

            // Flash the live indicator
            const ind = document.getElementById('liveIndicator');
            ind.classList.replace('bg-success', 'bg-warning');
            setTimeout(() => ind.classList.replace('bg-warning', 'bg-success'), 1000);
        }
    } catch(e) { console.warn('Dashboard poll failed:', e); }
}

// Poll every 10 seconds
setInterval(pollAdminDashboard, 10000);
</script>
@endpush
