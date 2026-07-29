@extends('layouts.app')
@section('title', 'My Sessions')
@section('page-title', 'Class Sessions')

@push('styles')
<style>
/* ── compact session-type pill toggle ─────────────── */
.stype-radio { display:none; }
.stype-pill {
    display:inline-flex; align-items:center; gap:6px;
    border:1.5px solid #e2e8f0; border-radius:50px;
    padding:6px 16px; cursor:pointer; font-size:13px;
    font-weight:600; color:#64748b; background:#fff;
    transition:all .18s; user-select:none;
    white-space:nowrap;
}
.stype-pill:hover { border-color:#94a3b8; color:#334155; }
.stype-radio:checked + .stype-pill          { border-color:#4f46e5; background:#eef2ff; color:#4f46e5; }
.stype-radio.out:checked + .stype-pill      { border-color:#dc2626; background:#fef2f2; color:#dc2626; }

/* session-type badge in history table */
.badge-morning    { background:#fef9c3; color:#854d0e; font-weight:700; }
.badge-afternoon  { background:#fee2e2; color:#991b1b; font-weight:700; }
</style>
@endpush

@section('content')

{{-- Start New Session --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">
            <i class="bi bi-play-circle-fill text-success me-2"></i>Start a New Class Session
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.sessions.start') }}" method="POST" id="sessionForm">
            @csrf
            <div class="row g-3 align-items-end">

                {{-- Subject --}}
                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Section --}}
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <input type="text" name="section"
                           class="form-control @error('section') is-invalid @enderror"
                           value="{{ old('section') }}" placeholder="e.g. Grade 7-A">
                    @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Camera --}}
                <div class="col-md-3">
                    <label class="form-label">Camera</label>
                    <select name="camera_id" id="cameraSelect"
                            class="form-select @error('camera_id') is-invalid @enderror"
                            onchange="handleCameraChange(this)">
                        <option value="">— Select Camera —</option>
                        @foreach($cameras as $camera)
                            <option value="{{ $camera->id }}"
                                    data-local="{{ $camera->is_local_device ? '1' : '0' }}"
                                    {{ old('camera_id') == $camera->id ? 'selected' : '' }}>
                                @if($camera->is_local_device)
                                    📱 {{ $camera->name }} (This Device)
                                @else
                                    🎥 {{ $camera->name }} ({{ $camera->location }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('camera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Session Type — compact pill toggle --}}
                <div class="col-md-3">
                    <label class="form-label">
                        Session Type
                        @error('session_type')<span class="text-danger small"> {{ $message }}</span>@enderror
                    </label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="stype-radio" name="session_type"
                               id="stMorning" value="morning_in"
                               {{ old('session_type','morning_in') === 'morning_in' ? 'checked' : '' }}>
                        <label for="stMorning" class="stype-pill">
                            🌅 Morning In
                        </label>

                        <input type="radio" class="stype-radio out" name="session_type"
                               id="stAfternoon" value="afternoon_out"
                               {{ old('session_type') === 'afternoon_out' ? 'checked' : '' }}>
                        <label for="stAfternoon" class="stype-pill">
                            🌇 Afternoon Out
                        </label>
                    </div>
                </div>

                {{-- Start button --}}
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-play-fill"></i> Start
                    </button>
                </div>
            </div>

            {{-- Local device info (shown only when local cam selected) --}}
            <div id="localCamInfo" style="display:none;" class="mt-3">
                <div class="alert alert-primary d-flex align-items-start gap-3 mb-0 py-2">
                    <i class="bi bi-laptop fs-5 mt-1 flex-shrink-0"></i>
                    <div class="small">
                        <strong>Using Your Device Camera</strong> — your browser will request
                        permission when the session starts. Works on laptop webcam or phone camera.
                        <div id="devicePickerWrap" style="display:none;" class="mt-1">
                            <select class="form-select form-select-sm" id="devicePicker"
                                    style="max-width:320px;" onchange="saveDeviceChoice(this.value)">
                                <option value="">— Auto (browser default) —</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Session History --}}
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Session History</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Type</th>
                    <th>Camera</th>
                    <th>Started</th>
                    <th>Ended</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-semibold">{{ $session->subject }}</td>
                    <td>{{ $session->section }}</td>
                    <td>
                        @if($session->session_type === 'afternoon_out')
                            <span class="badge badge-afternoon">🌇 Afternoon</span>
                        @else
                            <span class="badge badge-morning">🌅 Morning</span>
                        @endif
                    </td>
                    <td>
                        @if($session->camera->is_local_device)
                            <i class="bi bi-laptop text-primary me-1"></i>
                        @endif
                        {{ $session->camera->location }}
                    </td>
                    <td>{{ $session->started_at?->format('M d, h:i A') ?? '—' }}</td>
                    <td>{{ $session->ended_at?->format('h:i A') ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $session->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('teacher.sessions.live', $session) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye-fill"></i> View
                        </a>
                        @if($session->status === 'active')
                            <a href="{{ route('teacher.sessions.camera', $session) }}"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-camera-video-fill"></i> Camera
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $sessions->links() }}

@push('scripts')
<script>
function handleCameraChange(sel) {
    const isLocal = sel.options[sel.selectedIndex]?.dataset.local === '1';
    document.getElementById('localCamInfo').style.display = isLocal ? '' : 'none';
    if (isLocal) enumerateDevices();
}

async function enumerateDevices() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const ts = await navigator.mediaDevices.getUserMedia({ video:true, audio:false });
        ts.getTracks().forEach(t => t.stop());
        const vdevs = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind==='videoinput');
        if (vdevs.length < 2) return;
        const picker = document.getElementById('devicePicker');
        const wrap   = document.getElementById('devicePickerWrap');
        while (picker.options.length > 1) picker.remove(1);
        vdevs.forEach((d,i) => {
            const o = document.createElement('option');
            o.value = d.deviceId; o.textContent = d.label || `Camera ${i+1}`;
            picker.appendChild(o);
        });
        const saved = sessionStorage.getItem('preferredCameraDeviceId');
        if (saved) picker.value = saved;
        wrap.style.display = '';
    } catch(e) {}
}

function saveDeviceChoice(v) {
    v ? sessionStorage.setItem('preferredCameraDeviceId', v)
      : sessionStorage.removeItem('preferredCameraDeviceId');
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('cameraSelect');
    if (sel.value) handleCameraChange(sel);
});
</script>
@endpush
@endsection

@push('styles')
<style>
/* ── Session type cards ────────────────────────────────────────── */
.stype-option { display: none; }
.stype-label {
    display: flex; align-items: center; gap: 14px;
    border: 2px solid #e2e8f0; border-radius: 14px;
    padding: 14px 18px; cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #fff; user-select: none;
}
.stype-label:hover { border-color: #94a3b8; background: #f8fafc; }
.stype-option:checked + .stype-label { border-color: #4f46e5; background: #eef2ff; }
.stype-option.out:checked + .stype-label { border-color: #dc2626; background: #fef2f2; }
.stype-icon { font-size: 28px; flex-shrink: 0; }
.stype-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.stype-desc  { font-size: 12px; color: #64748b; }

/* ── PH clock ──────────────────────────────────────────────────── */
.ph-clock {
    display: inline-flex; align-items: center; gap: 8px;
    background: #0f172a; color: #fff;
    border-radius: 12px; padding: 8px 16px;
    font-size: 14px; font-weight: 700;
    font-family: 'Courier New', monospace;
}
.ph-clock .ph-time { font-size: 20px; color: #4ade80; }
.ph-clock .ph-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }

/* ── Session type badge in history table ───────────────────────── */
.badge-morning { background: #fef3c7; color: #92400e; }
.badge-afternoon { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')

{{-- PH Real-Time Clock --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="ph-clock" id="phClock">
        <div>
            <div class="ph-label">Philippine Time</div>
            <div class="ph-time" id="phTime">--:-- --</div>
        </div>
        <div>
            <div class="ph-label">Date</div>
            <div style="font-size:13px;color:#e2e8f0;" id="phDate">---</div>
        </div>
    </div>
    <small class="text-muted">All timestamps are recorded in Philippine Standard Time (PST)</small>
</div>

{{-- Start New Session --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">
            <i class="bi bi-play-circle-fill text-success me-2"></i>Start a New Class Session
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.sessions.start') }}" method="POST" id="sessionForm">
            @csrf

            {{-- Row 1: Subject + Section + Camera --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Subject</label>
                    <input type="text" name="subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Section</label>
                    <input type="text" name="section"
                           class="form-control @error('section') is-invalid @enderror"
                           value="{{ old('section') }}" placeholder="e.g. Grade 7 — A">
                    @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Camera</label>
                    <select name="camera_id" id="cameraSelect"
                            class="form-select @error('camera_id') is-invalid @enderror"
                            onchange="handleCameraChange(this)">
                        <option value="">— Select Camera —</option>
                        @foreach($cameras as $camera)
                            <option value="{{ $camera->id }}"
                                    data-local="{{ $camera->is_local_device ? '1' : '0' }}"
                                    {{ old('camera_id') == $camera->id ? 'selected' : '' }}>
                                @if($camera->is_local_device)
                                    📱 {{ $camera->name }} (This Device)
                                @else
                                    🎥 {{ $camera->name }} ({{ $camera->location }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('camera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Row 2: Session Type (big card selector) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Session Type <span class="text-muted fw-normal">(determines default scan mode)</span>
                </label>
                @error('session_type')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="radio" class="stype-option" name="session_type"
                               id="stMorning" value="morning_in"
                               {{ old('session_type', 'morning_in') === 'morning_in' ? 'checked' : '' }}>
                        <label for="stMorning" class="stype-label w-100">
                            <div class="stype-icon">🌅</div>
                            <div>
                                <div class="stype-title">Morning — Time In</div>
                                <div class="stype-desc">
                                    Camera auto-starts in <strong>Time-In</strong> mode.
                                    Students scan to record their <strong>arrival</strong>.
                                    Parents get a "has arrived" email.
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <input type="radio" class="stype-option out" name="session_type"
                               id="stAfternoon" value="afternoon_out"
                               {{ old('session_type') === 'afternoon_out' ? 'checked' : '' }}>
                        <label for="stAfternoon" class="stype-label w-100">
                            <div class="stype-icon">🌇</div>
                            <div>
                                <div class="stype-title">Afternoon — Time Out</div>
                                <div class="stype-desc">
                                    Camera auto-starts in <strong>Time-Out</strong> mode.
                                    Students scan to record their <strong>departure</strong>.
                                    Parents get a "has left" email with duration.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Local device info --}}
            <div id="localCamInfo" style="display:none;" class="mb-3">
                <div class="alert alert-primary d-flex align-items-start gap-3 mb-0 py-3">
                    <i class="bi bi-laptop fs-4 mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold">Using Your Device Camera</div>
                        <div class="small">
                            Your browser will ask for camera permission when the session starts.
                            Use your <strong>built-in webcam</strong> or open on a
                            <strong>phone/tablet</strong> for its camera.
                        </div>
                        <div class="mt-2" id="devicePickerWrap" style="display:none;">
                            <label class="form-label small mb-1">Choose Camera Device</label>
                            <select class="form-select form-select-sm" id="devicePicker"
                                    style="max-width:360px;" onchange="saveDeviceChoice(this.value)">
                                <option value="">— Auto (browser default) —</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success px-4" id="startBtn">
                <i class="bi bi-play-fill me-1"></i> Start Session
            </button>
        </form>
    </div>
</div>

{{-- Session History --}}
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Session History</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Type</th>
                    <th>Camera</th>
                    <th>Started (PST)</th>
                    <th>Ended</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-semibold">{{ $session->subject }}</td>
                    <td>{{ $session->section }}</td>
                    <td>
                        @if($session->session_type === 'afternoon_out')
                            <span class="badge badge-afternoon">🌇 Afternoon Out</span>
                        @else
                            <span class="badge badge-morning">🌅 Morning In</span>
                        @endif
                    </td>
                    <td>
                        @if($session->camera->is_local_device)
                            <span class="badge bg-primary me-1"><i class="bi bi-laptop"></i></span>
                        @endif
                        {{ $session->camera->location }}
                    </td>
                    <td>{{ $session->started_at?->format('M d, Y h:i A') ?? '—' }}</td>
                    <td>{{ $session->ended_at?->format('h:i A') ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $session->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('teacher.sessions.live', $session) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye-fill"></i> View
                        </a>
                        @if($session->status === 'active')
                            <a href="{{ route('teacher.sessions.camera', $session) }}"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-camera-video-fill"></i> Camera
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $sessions->links() }}

@push('scripts')
<script>
// ── PH Real-Time Clock ─────────────────────────────────────────────────────────
function updatePhClock() {
    const now = new Date();
    const opts = { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    const dateOpts = { timeZone: 'Asia/Manila', weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
    document.getElementById('phTime').textContent = now.toLocaleTimeString('en-PH', opts);
    document.getElementById('phDate').textContent = now.toLocaleDateString('en-PH', dateOpts);
}
updatePhClock();
setInterval(updatePhClock, 1000);

// ── Camera change ──────────────────────────────────────────────────────────────
function handleCameraChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const isLocal = opt && opt.dataset.local === '1';
    document.getElementById('localCamInfo').style.display = isLocal ? '' : 'none';
    if (isLocal) enumerateDevices();
}

async function enumerateDevices() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const ts = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        ts.getTracks().forEach(t => t.stop());
        const devices = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind === 'videoinput');
        if (devices.length < 1) return;
        const picker = document.getElementById('devicePicker');
        const wrap   = document.getElementById('devicePickerWrap');
        while (picker.options.length > 1) picker.remove(1);
        devices.forEach((dev, i) => {
            const o = document.createElement('option');
            o.value = dev.deviceId;
            o.textContent = dev.label || `Camera ${i + 1}`;
            picker.appendChild(o);
        });
        const saved = sessionStorage.getItem('preferredCameraDeviceId');
        if (saved) picker.value = saved;
        wrap.style.display = devices.length > 1 ? '' : 'none';
    } catch (e) {}
}

function saveDeviceChoice(deviceId) {
    deviceId ? sessionStorage.setItem('preferredCameraDeviceId', deviceId)
             : sessionStorage.removeItem('preferredCameraDeviceId');
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('cameraSelect');
    if (sel.value) handleCameraChange(sel);
});
</script>
@endpush
@endsection
