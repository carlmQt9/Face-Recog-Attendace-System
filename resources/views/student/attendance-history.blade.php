@extends('layouts.app')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance History')

@section('content')

{{-- Student Profile Banner --}}
@php
    $student = auth()->user()->student;
    $profileImg = ($student?->face_encoding && Storage::disk('public')->exists($student->face_encoding))
        ? Storage::url($student->face_encoding)
        : null;
@endphp
<div class="card mb-4" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);border:none;">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center gap-3">
            @if($profileImg)
                <img src="{{ $profileImg }}"
                     alt="{{ auth()->user()->name }}"
                     data-lightbox="{{ $profileImg }}"
                     data-lightbox-caption="{{ auth()->user()->name }}"
                     data-lightbox-sub="Registered Face"
                     style="width:60px;height:60px;border-radius:12px;object-fit:cover;
                            border:3px solid rgba(255,255,255,.4);flex-shrink:0;cursor:zoom-in;">
            @else
                <div style="width:60px;height:60px;border-radius:12px;flex-shrink:0;
                            background:rgba(255,255,255,.2);
                            display:flex;align-items:center;justify-content:center;
                            font-size:26px;color:#fff;font-weight:800;
                            border:3px solid rgba(255,255,255,.3);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="font-size:18px;font-weight:800;color:#fff;">{{ auth()->user()->name }}</div>
                <div style="font-size:13px;color:rgba(255,255,255,.75);">
                    Student ID: {{ $student?->student_id ?? '—' }}
                    @if($student?->section) &nbsp;·&nbsp; {{ $student->section }} @endif
                    @if($student?->grade_level) &nbsp;·&nbsp; {{ $student->grade_level }} @endif
                </div>
                <div style="margin-top:4px;">
                    @if($student?->face_registered)
                        <span style="font-size:11px;background:rgba(74,222,128,.2);color:#4ade80;
                                     border:1px solid rgba(74,222,128,.3);border-radius:50px;
                                     padding:2px 10px;font-weight:700;">
                            ✓ Face Registered
                        </span>
                    @else
                        <span style="font-size:11px;background:rgba(251,146,60,.2);color:#fb923c;
                                     border:1px solid rgba(251,146,60,.3);border-radius:50px;
                                     padding:2px 10px;font-weight:700;">
                            ⚠ Face Not Registered
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
    <div class="card-header bg-white p-0" style="border-bottom:none;">
        {{-- Pinned: most recent record ─────────────────────── --}}
        @if($latest)
        @php
            $latestSnap = $latest->snapshotUrl()
                ?? ($student?->face_encoding && Storage::disk('public')->exists($student->face_encoding)
                    ? Storage::url($student->face_encoding) : null);
        @endphp
        <div style="background:linear-gradient(135deg,rgba(79,70,229,.06),rgba(6,182,212,.04));
                    border-bottom:1px solid #e2e8f0;padding:14px 20px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;
                        letter-spacing:.08em;color:#94a3b8;margin-bottom:8px;">
                📌 Most Recent
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($latestSnap)
                        <img src="{{ $latestSnap }}"
                             alt="{{ auth()->user()->name }}"
                             data-lightbox="{{ $latestSnap }}"
                             data-lightbox-caption="{{ auth()->user()->name }}"
                             data-lightbox-sub="{{ $latest->scan_type === 'time_out' ? 'Time Out' : 'Time In' }} · {{ $latest->arrived_at->format('h:i A') }}"
                             style="width:48px;height:48px;border-radius:10px;object-fit:cover;
                                    border:2px solid #e2e8f0;flex-shrink:0;cursor:zoom-in;">
                    @else
                        <div style="width:48px;height:48px;border-radius:10px;flex-shrink:0;
                                    background:linear-gradient(135deg,#4f46e5,#06b6d4);
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:20px;color:#fff;font-weight:800;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div style="font-size:15px;font-weight:800;color:#0f172a;">
                            {{ $latest->arrived_at->format('l, F j, Y') }}
                        </div>
                        <div style="font-size:12px;color:#64748b;margin-top:1px;">
                            📍 {{ $latest->camera->location }}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-4 text-center">
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Time In</div>
                        <div style="font-size:17px;font-weight:800;color:#16a34a;">{{ $latest->arrived_at->format('h:i A') }}</div>
                    </div>
                    <div style="width:1px;background:#e2e8f0;"></div>
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Time Out</div>
                        <div style="font-size:17px;font-weight:800;color:{{ $latest->time_out ? '#dc2626' : '#94a3b8' }};">
                            {{ $latest->time_out ? $latest->time_out->format('h:i A') : '—' }}
                        </div>
                    </div>
                    <div style="width:1px;background:#e2e8f0;"></div>
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Duration</div>
                        <div style="font-size:17px;font-weight:800;color:#4f46e5;">{{ $latest->durationLabel() }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table header row ──────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center px-4 py-2" style="border-bottom:1px solid #f1f5f9;">
            <h6 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Arrival Details
                <span id="attendanceLive" class="ms-2 badge bg-success" style="font-size:10px;vertical-align:middle;">● Live</span>
            </h6>
            <span id="attendanceCount" class="text-muted small">{{ $records->count() }} records</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">Photo</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody id="attendanceTableBody">
                @php $sortedRecords = $records->sortByDesc('arrived_at'); @endphp
                @forelse($sortedRecords as $record)
                    @php
                        $snap = $record->snapshotUrl()
                            ?? ($student?->face_encoding && Storage::disk('public')->exists($student->face_encoding)
                                ? Storage::url($student->face_encoding)
                                : null);
                        $snapSub = ($record->scan_type === 'time_out' ? 'Time Out' : 'Time In')
                            . ' · ' . $record->arrived_at->format('h:i A')
                            . ' · ' . $record->arrived_at->format('M j, Y');
                    @endphp
                    <tr>
                        <td>
                            @if($snap)
                                <img src="{{ $snap }}"
                                     alt="{{ auth()->user()->name }}"
                                     data-lightbox="{{ $snap }}"
                                     data-lightbox-caption="{{ auth()->user()->name }}"
                                     data-lightbox-sub="{{ $snapSub }}"
                                     style="width:44px;height:44px;border-radius:9px;
                                            object-fit:cover;border:1.5px solid #e2e8f0;cursor:zoom-in;">
                            @else
                                <div style="width:44px;height:44px;border-radius:9px;
                                            background:linear-gradient(135deg,#4f46e5,#06b6d4);
                                            display:flex;align-items:center;justify-content:center;
                                            font-size:18px;color:#fff;font-weight:800;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">{{ $record->arrived_at->format('D, M j') }}</td>
                        <td style="vertical-align:middle;">
                            <span class="fw-semibold text-success">{{ $record->arrived_at->format('h:i A') }}</span>
                        </td>
                        <td style="vertical-align:middle;">
                            @if($record->time_out)
                                <span class="fw-semibold text-danger">{{ $record->time_out->format('h:i A') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">
                            <span class="text-muted small">{{ $record->durationLabel() }}</span>
                        </td>
                        <td style="vertical-align:middle;">{{ $record->camera->location }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No attendance records this month.</td></tr>
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

        // Only update DOM if count changed — prevents wiping images unnecessarily
        const countEl = document.getElementById('attendanceCount');
        const currentCount = parseInt(countEl?.textContent ?? '0');
        if (data.total === currentCount) return;

        countEl.textContent = data.total + ' records';

        const tbody = document.getElementById('attendanceTableBody');
        if (!tbody) return;

        const studentInitial = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}';
        const studentName    = '{{ addslashes(auth()->user()->name) }}';

        if (!data.records.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No attendance records this month.</td></tr>';
            return;
        }

        tbody.innerHTML = data.records.map(r => {
            const photoHtml = r.snapshot_url
                ? `<img src="${r.snapshot_url}"
                        alt="${studentName}"
                        data-lightbox="${r.snapshot_url}"
                        data-lightbox-caption="${studentName}"
                        data-lightbox-sub="${r.scan_type === 'time_out' ? 'Time Out' : 'Time In'} · ${r.time_in} · ${r.date}"
                        style="width:44px;height:44px;border-radius:9px;object-fit:cover;
                               border:1.5px solid #e2e8f0;cursor:zoom-in;">`
                : `<div style="width:44px;height:44px;border-radius:9px;
                               background:linear-gradient(135deg,#4f46e5,#06b6d4);
                               display:flex;align-items:center;justify-content:center;
                               font-size:18px;color:#fff;font-weight:800;">${studentInitial}</div>`;

            return `<tr>
                <td>${photoHtml}</td>
                <td style="vertical-align:middle;">${r.date}</td>
                <td style="vertical-align:middle;"><span class="fw-semibold text-success">${r.time_in}</span></td>
                <td style="vertical-align:middle;">${r.time_out !== '—' ? `<span class="fw-semibold text-danger">${r.time_out}</span>` : '<span class="text-muted">—</span>'}</td>
                <td style="vertical-align:middle;"><span class="text-muted small">${r.duration}</span></td>
                <td style="vertical-align:middle;">${r.camera}</td>
            </tr>`;
        }).join('');

        // Flash indicator
        const ind = document.getElementById('attendanceLive');
        if (ind) {
            ind.classList.replace('bg-success', 'bg-warning');
            setTimeout(() => ind.classList.replace('bg-warning', 'bg-success'), 800);
        }
    } catch(e) { console.warn('Attendance poll failed:', e); }
}

// Poll every 15 seconds
setInterval(pollAttendance, 15000);
</script>
@endpush
