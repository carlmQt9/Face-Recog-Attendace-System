@extends('layouts.app')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance History')

@section('content')
{{-- Month Picker --}}
<div class="d-flex gap-2 mb-4 align-items-center">
    <form method="GET" action="{{ route('student.attendance.index') }}" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0 fw-semibold">Month:</label>
        <select name="month" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </select>
        <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
    <span class="text-muted small ms-2">{{ $records->count() }} days present</span>
</div>

{{-- Calendar Grid --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">
            <i class="bi bi-calendar3 me-2 text-primary"></i>
            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
        </h6>
    </div>
    <div class="card-body">
        @php
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $firstDay    = date('N', mktime(0,0,0,$month,1,$year)); // 1=Mon … 7=Sun
            $presentDays = $calendar->keys()->map(fn($d) => (int) substr($d, 8, 2))->toArray();
        @endphp

        <div class="d-grid" style="grid-template-columns: repeat(7, 1fr); display:grid; gap:4px; text-align:center;">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow)
                <div class="fw-bold small text-muted py-1">{{ $dow }}</div>
            @endforeach

            {{-- empty cells before first day --}}
            @for($i = 1; $i < $firstDay; $i++)
                <div></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php $isPresent = in_array($day, $presentDays); @endphp
                <div class="py-2 rounded {{ $isPresent ? 'bg-success text-white' : 'bg-light text-muted' }}"
                     style="border-radius:8px; min-width:34px;">
                    {{ $day }}
                    @if($isPresent)<br><i class="bi bi-check-lg" style="font-size:10px;"></i>@endif
                </div>
            @endfor
        </div>
    </div>
</div>

{{-- Detail Table --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>Arrival Details
            <span id="attendanceLive" class="ms-2 badge bg-success" style="font-size:10px;vertical-align:middle;">● Live</span>
        </h6>
        <span id="attendanceCount" class="text-muted small">{{ $records->count() }} records</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Duration</th><th>Location</th></tr>
            </thead>
            <tbody id="attendanceTableBody">
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->arrived_at->format('D, M j') }}</td>
                        <td><span class="fw-semibold text-success">{{ $record->arrived_at->format('h:i A') }}</span></td>
                        <td>
                            @if($record->time_out)
                                <span class="fw-semibold text-danger">{{ $record->time_out->format('h:i A') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="text-muted small">{{ $record->durationLabel() }}</span></td>
                        <td>{{ $record->camera->location }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No attendance records this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ATTENDANCE_URL = '{{ route('student.attendance.index') }}?month={{ $month }}&year={{ $year }}';

async function pollAttendance() {
    // Only poll if viewing current month
    const now = new Date();
    if ({{ $month }} !== now.getMonth() + 1 || {{ $year }} !== now.getFullYear()) return;

    try {
        const resp = await fetch(ATTENDANCE_URL, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        if (!resp.ok) return;
        const data = await resp.json();

        document.getElementById('attendanceCount').textContent = data.total + ' records';

        const tbody = document.getElementById('attendanceTableBody');
        if (!tbody) return;

        if (!data.records.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No attendance records this month.</td></tr>';
            return;
        }

        tbody.innerHTML = data.records.map(r => `
            <tr>
                <td>${r.date}</td>
                <td><span class="fw-semibold text-success">${r.time_in}</span></td>
                <td>${r.time_out !== '—' ? `<span class="fw-semibold text-danger">${r.time_out}</span>` : '<span class="text-muted">—</span>'}</td>
                <td><span class="text-muted small">${r.duration}</span></td>
                <td>${r.camera}</td>
            </tr>`).join('');

        // Flash indicator
        const ind = document.getElementById('attendanceLive');
        if (ind) {
            ind.classList.replace('bg-success', 'bg-warning');
            setTimeout(() => ind.classList.replace('bg-warning', 'bg-success'), 800);
        }
    } catch(e) { console.warn('Attendance poll failed:', e); }
}

// Poll every 15 seconds (student page doesn't need as fast as teacher)
setInterval(pollAttendance, 15000);
</script>
@endpush
