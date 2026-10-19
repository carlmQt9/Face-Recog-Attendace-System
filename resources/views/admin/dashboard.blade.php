@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@push('styles')
<style>
.stat-card { border-radius:14px; padding:22px 20px; color:#fff; position:relative; overflow:hidden; }
.stat-card .stat-icon { position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:48px; opacity:.18; }
.stat-card .stat-num  { font-size:34px; font-weight:900; line-height:1; }
.stat-card .stat-lbl  { font-size:13px; opacity:.85; margin-top:4px; }
.chart-card { background:#fff; border-radius:14px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.07); height:100%; }
.chart-card .chart-title { font-size:14px; font-weight:700; color:#1e293b; margin-bottom:4px; }
.chart-card .chart-sub   { font-size:12px; color:#94a3b8; margin-bottom:16px; }
</style>
@endpush

@section('content')

{{-- ══ WELCOME BANNER ══ --}}
@php
    $hour = now()->format('G');
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
    $greetingIcon = $hour < 12 ? '🌤️' : ($hour < 18 ? '☀️' : '🌙');
    $firstName = explode(' ', auth()->user()->name)[0];
@endphp
<div class="mb-4" style="background:linear-gradient(135deg,#0f3318 0%,#1a5c2a 100%);border-radius:16px;padding:24px 28px;position:relative;overflow:hidden;">
    {{-- decorative orbs --}}
    <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(245,168,0,.12);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-50px;left:30%;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none;"></div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:13px;color:rgba(255,255,255,.6);font-weight:500;letter-spacing:.04em;text-transform:uppercase;margin-bottom:4px;">
                {{ $greetingIcon }} {{ $greeting }}
            </div>
            <div style="font-size:26px;font-weight:800;color:#fff;line-height:1.1;">
                Welcome back, <span style="color:#f5a800;">{{ $firstName }}</span> 👋
            </div>
            <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px;">
                {{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; DMCMES Smart Attendance
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Logged in as</div>
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:8px 14px;">
                <div style="width:30px;height:30px;border-radius:8px;background:#f5a800;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#0f3318;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#ffffff;">{{ auth()->user()->name }}</div>
                    <div style="font-size:11px;color:#f5a800;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Administrator</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a5c2a,#0f3318)">
            <div class="stat-num" id="stat-students">{{ $stats['total_students'] }}</div>
            <div class="stat-lbl">Total Students</div>
            <div class="stat-icon">🎒</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#2d7a3a,#1a5c2a)">
            <div class="stat-num" id="stat-teachers">{{ $stats['total_teachers'] }}</div>
            <div class="stat-lbl">Total Teachers</div>
            <div class="stat-icon">👩‍🏫</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#b45309,#d97706)">
            <div class="stat-num" id="stat-cameras">{{ $stats['active_cameras'] }}<span style="font-size:18px;opacity:.7;">/{{ $stats['total_cameras'] }}</span></div>
            <div class="stat-lbl">Active Cameras</div>
            <div class="stat-icon">📷</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#c2800a,#f5a800)">
            <div class="stat-num" id="stat-attendance">{{ $stats['today_attendance'] }}</div>
            <div class="stat-lbl">Present Today</div>
            <div class="stat-icon">✅</div>
        </div>
    </div>
</div>

@if($stats['local_cameras'] == 0)
{{-- ══ LOCAL CAMERA STATUS ══ --}}
<div class="alert alert-warning d-flex align-items-center mb-4" role="alert" style="border-radius:12px;border:none;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div>
        <strong>No Local Cameras Active!</strong> 
        Set up a local camera to enable device-based face recognition. 
        <a href="{{ route('admin.cameras.index') }}" class="alert-link">Manage cameras</a>
    </div>
</div>
@else
{{-- ══ LOCAL CAMERA READY ══ --}}
<div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius:12px;border:none;">
    <i class="bi bi-camera-video-fill me-2"></i>
    <div>
        <strong>{{ $stats['local_cameras'] }} Local Camera(s) Active!</strong> 
        Your device cameras are ready for face recognition attendance. 
        <a href="{{ route('admin.cameras.index') }}" class="alert-link">Manage cameras</a>
    </div>
</div>
@endif

{{-- ══ CHARTS ROW ══ --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-5">
        <div class="chart-card">
            <div class="chart-title">📊 Attendance — Last 7 Days</div>
            <div class="chart-sub">Total students marked present per day</div>
            <canvas id="chartWeekly" height="180"></canvas>
        </div>
    </div>
    <div class="col-12 col-md-5">
        <div class="chart-card">
            <div class="chart-title">📈 Today's Hourly Trend</div>
            <div class="chart-sub">Scan activity hour by hour today</div>
            <canvas id="chartHourly" height="180"></canvas>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="chart-card d-flex flex-column align-items-center justify-content-center">
            <div class="chart-title text-center">🔍 Method</div>
            <div class="chart-sub text-center">Today's scans</div>
            <canvas id="chartMethod" style="max-width:140px;max-height:140px;"></canvas>
            <div id="methodLegend" class="mt-3 w-100" style="font-size:12px;"></div>
        </div>
    </div>
</div>

{{-- ══ RECENT ATTENDANCE TABLE ══ --}}
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
            <table class="table table-hover mb-0 table-card-mobile" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th class="d-none d-sm-table-cell">Camera / Location</th>
                        <th class="d-none d-sm-table-cell">Method</th>
                        <th>Time</th>
                        <th class="d-none d-md-table-cell">Notified</th>
                    </tr>
                </thead>
                <tbody id="recentTableBody">
                    @forelse($recentAttendance as $record)
                    <tr>
                        <td class="td-main">
                            <div class="fw-semibold">{{ $record->student->user->name }}</div>
                            <div class="text-muted d-sm-none" style="font-size:11px;">{{ $record->camera->location }}</div>
                        </td>
                        <td class="td-hide d-none d-sm-table-cell">{{ $record->camera->location }}</td>
                        <td class="td-hide d-none d-sm-table-cell">
                            <span class="badge {{ $record->method === 'manual' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ ucfirst(str_replace('_', ' ', $record->method)) }}
                            </span>
                        </td>
                        <td class="td-badge">
                            <span class="badge bg-success">{{ $record->arrived_at->format('h:i A') }}</span>
                        </td>
                        <td class="td-hide d-none d-md-table-cell">
                            @if($record->notification_sent)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle-fill text-secondary"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="5" class="text-center text-muted py-4">No attendance records today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// ── Shared chart defaults ─────────────────────────────────────────────────────
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#64748b';

// ── 1. Weekly Bar Chart ───────────────────────────────────────────────────────
const weeklyLabels = {!! json_encode($last7->pluck('label')) !!};
const weeklyData   = {!! json_encode($last7->pluck('count')) !!};

const chartWeekly = new Chart(document.getElementById('chartWeekly'), {
    type: 'bar',
    data: {
        labels: weeklyLabels,
        datasets: [{
            label: 'Present',
            data: weeklyData,
            backgroundColor: weeklyData.map((_, i) =>
                i === weeklyData.length - 1 ? '#0c3d8a' : '#4a7fd4'
            ),
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }
        }
    }
});

// ── 2. Hourly Line Chart ──────────────────────────────────────────────────────
const hourLabels = {!! json_encode($hourLabels->values()) !!};
let   hourData   = {!! json_encode($hourData->values()) !!};

const chartHourly = new Chart(document.getElementById('chartHourly'), {
    type: 'line',
    data: {
        labels: hourLabels,
        datasets: [{
            label: 'Scans',
            data: hourData,
            borderColor: '#f5a800',
            backgroundColor: 'rgba(245,168,0,.12)',
            borderWidth: 2.5,
            pointBackgroundColor: '#f5a800',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } }
        }
    }
});

// ── 3. Method Doughnut ────────────────────────────────────────────────────────
@php
    $methods      = ['face_scan' => 'Face Scan', 'qr_code' => 'QR Code', 'manual' => 'Manual'];
    $methodColors = ['face_scan' => '#0c3d8a', 'qr_code' => '#f5a800', 'manual' => '#1a6b3c'];
    $mLabels      = [];
    $mData        = [];
    $mColors      = [];
    foreach ($methods as $key => $label) {
        $mLabels[] = $label;
        $mData[]   = $methodBreakdown->get($key, 0);
        $mColors[] = $methodColors[$key];
    }
@endphp
const methodLabels = {!! json_encode($mLabels) !!};
let   methodData   = {!! json_encode($mData) !!};
const methodColors = {!! json_encode($mColors) !!};

const chartMethod = new Chart(document.getElementById('chartMethod'), {
    type: 'doughnut',
    data: {
        labels: methodLabels,
        datasets: [{
            data: methodData,
            backgroundColor: methodColors,
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: { legend: { display: false }, tooltip: { callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.raw}`
        }}}
    }
});

// Build custom legend
function buildMethodLegend(labels, data, colors) {
    const el = document.getElementById('methodLegend');
    if (!el) return;
    el.innerHTML = labels.map((l, i) => `
        <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-1">
                <span style="width:10px;height:10px;border-radius:50%;background:${colors[i]};flex-shrink:0;display:inline-block;"></span>
                <span>${l}</span>
            </div>
            <strong>${data[i]}</strong>
        </div>`).join('');
}
buildMethodLegend(methodLabels, methodData, methodColors);

// ── Real-time polling ─────────────────────────────────────────────────────────
const STATS_URL  = '{{ route('admin.dashboard.stats') }}';
let lastTopName  = '{{ $recentAttendance->first()?->student->user->name ?? '' }}';

async function pollAdminDashboard() {
    try {
        const resp = await fetch(STATS_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (!resp.ok) return;
        const data = await resp.json();

        // Stat cards
        document.getElementById('stat-students').textContent   = data.stats.total_students;
        document.getElementById('stat-teachers').textContent   = data.stats.total_teachers;
        document.getElementById('stat-attendance').textContent = data.stats.today_attendance;
        
        // Update cameras stat with local camera info
        const cameraEl = document.getElementById('stat-cameras');
        cameraEl.innerHTML = `${data.stats.active_cameras}<span style="font-size:18px;opacity:.7;">/${data.stats.total_cameras}</span>`;

        // Update hourly chart live
        if (data.hourData) {
            chartHourly.data.datasets[0].data = data.hourData;
            chartHourly.update('none');
        }

        // Update method doughnut live
        if (data.methodBreakdown) {
            const newData = ['face_scan', 'qr_code', 'manual'].map(k => data.methodBreakdown[k] ?? 0);
            chartMethod.data.datasets[0].data = newData;
            chartMethod.update('none');
            buildMethodLegend(methodLabels, newData, methodColors);
        }

        // Update recent table only on new data
        if (data.recent.length > 0 && data.recent[0].name !== lastTopName) {
            lastTopName = data.recent[0].name;
            const tbody = document.getElementById('recentTableBody');
            tbody.innerHTML = data.recent.map(r => `
                <tr>
                    <td class="td-main">
                        <div class="fw-semibold">${r.name}</div>
                        <div class="text-muted d-sm-none" style="font-size:11px;">${r.camera}</div>
                    </td>
                    <td class="td-hide d-none d-sm-table-cell">${r.camera}</td>
                    <td class="td-hide d-none d-sm-table-cell"><span class="badge ${r.method === 'Manual' ? 'bg-warning text-dark' : 'bg-success'}">${r.method}</span></td>
                    <td class="td-badge"><span class="badge bg-success">${r.time}</span></td>
                    <td class="td-hide d-none d-md-table-cell">${r.notified ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-secondary"></i>'}</td>
                </tr>`).join('');

            const ind = document.getElementById('liveIndicator');
            ind.classList.replace('bg-success', 'bg-warning');
            setTimeout(() => ind.classList.replace('bg-warning', 'bg-success'), 1000);
        }
    } catch(e) { console.warn('Dashboard poll failed:', e); }
}

setInterval(pollAdminDashboard, 10000);
</script>
@endpush
