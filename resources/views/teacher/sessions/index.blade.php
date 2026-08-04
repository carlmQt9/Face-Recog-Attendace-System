@extends('layouts.app')
@section('title', 'My Sessions')
@section('page-title', 'Class Sessions')

@push('styles')
<style>
/* ── session type pill toggle ─────────────────────── */
.stype-radio { display:none; }
.stype-pill {
    display:inline-flex; align-items:center; gap:6px;
    border:1.5px solid #e2e8f0; border-radius:50px;
    padding:6px 16px; cursor:pointer; font-size:13px;
    font-weight:600; color:#64748b; background:#fff;
    transition:all .18s; user-select:none; white-space:nowrap;
}
.stype-pill:hover { border-color:#94a3b8; color:#334155; }
.stype-radio:checked + .stype-pill     { border-color:#4f46e5; background:#eef2ff; color:#4f46e5; }
.stype-radio.out:checked + .stype-pill { border-color:#dc2626; background:#fef2f2; color:#dc2626; }
.stype-pill.disabled {
    opacity:.38; cursor:not-allowed; pointer-events:none;
    background:#f8fafc; border-color:#e2e8f0; color:#94a3b8;
}
.stype-sm {
    padding:4px 10px;
    font-size:12px;
}
.badge-morning   { background:#fef9c3; color:#854d0e; font-weight:700; }
.badge-afternoon { background:#fee2e2; color:#991b1b; font-weight:700; }

/* ── Slide-in drawer ──────────────────────────────── */
.drawer-backdrop {
    position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:1040; opacity:0; pointer-events:none; transition:opacity .3s;
}
.drawer-backdrop.open { opacity:1; pointer-events:all; }
.drawer {
    position:fixed; top:0; right:0; bottom:0;
    width:min(96vw,1100px); background:#fff; z-index:1050;
    transform:translateX(100%);
    transition:transform .32s cubic-bezier(.4,0,.2,1);
    display:flex; flex-direction:column;
    box-shadow:-8px 0 40px rgba(0,0,0,.18);
}
.drawer.open { transform:translateX(0); }
.drawer-header {
    display:flex; align-items:center; gap:14px;
    padding:14px 18px; border-bottom:1px solid #e2e8f0;
    background:#0f172a; flex-shrink:0;
}
.drawer-title { font-size:15px; font-weight:700; color:#fff; flex:1; }
.drawer-close {
    width:32px; height:32px; border-radius:8px;
    background:rgba(255,255,255,.1); border:none; color:#fff;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    font-size:17px; transition:background .2s;
}
.drawer-close:hover { background:rgba(255,255,255,.22); }
.drawer-body { flex:1; overflow:hidden; }
#drawerFrame { width:100%; height:100%; border:none; display:block; }
.drawer-loading {
    display:flex; align-items:center; justify-content:center;
    height:100%; gap:12px; color:#64748b; font-size:14px;
}
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
        <form action="{{ route('teacher.sessions.start') }}" method="POST" id="sessionForm"
              onsubmit="return validateSessionForm(event)">
            @csrf

            <div class="row g-2 align-items-start">
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Subject</label>
                    <input type="text" name="subject"
                           class="form-control form-control-sm @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Section</label>
                    <input type="text" name="section"
                           class="form-control form-control-sm @error('section') is-invalid @enderror"
                           value="{{ old('section') }}" placeholder="e.g. Grade 7-A">
                    @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Camera</label>
                    <select name="camera_id" id="cameraSelect"
                            class="form-select form-select-sm @error('camera_id') is-invalid @enderror"
                            onchange="handleCameraChange(this)">
                        <option value="">— Select —</option>
                        @foreach($cameras as $camera)
                            <option value="{{ $camera->id }}"
                                    data-local="{{ $camera->is_local_device ? '1' : '0' }}"
                                    {{ old('camera_id') == $camera->id ? 'selected' : '' }}>
                                {{ $camera->is_local_device ? '📱' : '🎥' }} {{ $camera->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('camera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1 fw-semibold" style="font-size:13px;">
                        Type
                        @error('session_type')<span class="text-danger"> {{ $message }}</span>@enderror
                    </label>
                    <div class="d-flex gap-1 flex-wrap" id="sessionTypeToggle">
                        <input type="radio" class="stype-radio" name="session_type"
                               id="stMorning" value="morning_in"
                               {{ old('session_type','morning_in') === 'morning_in' ? 'checked' : '' }}>
                        <label for="stMorning" class="stype-pill stype-sm" id="stMorningLabel">🌅 AM</label>
                        <input type="radio" class="stype-radio out" name="session_type"
                               id="stAfternoon" value="afternoon_out"
                               {{ old('session_type') === 'afternoon_out' ? 'checked' : '' }}>
                        <label for="stAfternoon" class="stype-pill stype-sm" id="stAfternoonLabel">🌇 PM</label>
                    </div>
                    <div id="sessionTypeHint" style="font-size:11px;margin-top:3px;min-height:16px;"></div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label mb-1 fw-semibold" style="font-size:13px;">
                        Schedule <span class="text-muted fw-normal" style="font-size:11px;">(optional)</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="time" name="scheduled_start" id="schedStart"
                               class="form-control @error('scheduled_start') is-invalid @enderror"
                               value="{{ old('scheduled_start') }}" oninput="onScheduleInput()">
                        <span class="input-group-text px-1 text-muted">–</span>
                        <input type="time" name="scheduled_end" id="schedEnd"
                               class="form-control @error('scheduled_end') is-invalid @enderror"
                               value="{{ old('scheduled_end') }}" oninput="onScheduleInput()">
                    </div>
                    <div id="scheduleHint" style="font-size:11px;margin-top:3px;min-height:16px;"></div>
                    @error('scheduled_end')<div class="text-danger" style="font-size:11px;">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-1" style="padding-top:23px;">
                    <button type="submit" class="btn btn-success btn-sm w-100 text-nowrap">
                        <i class="bi bi-play-fill"></i> Start
                    </button>
                </div>
            </div>

            <div id="localCamInfo" style="display:none;margin-top:10px;">
                <div class="alert alert-primary d-flex align-items-center gap-2 mb-0 py-2 px-3"
                     style="font-size:13px;">
                    <i class="bi bi-laptop flex-shrink-0"></i>
                    <span><strong>Local device camera</strong> — browser will ask for permission when session starts.</span>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Session History --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>Session History
            <span id="sessionsLive" class="ms-2 badge bg-success" style="font-size:10px;vertical-align:middle;">● Live</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>Subject</th><th>Section</th><th>Type</th>
                    <th class="d-none d-lg-table-cell">Schedule</th>
                    <th class="d-none d-md-table-cell">Camera</th>
                    <th>Started</th>
                    <th class="d-none d-sm-table-cell">Ended</th>
                    <th>Status</th><th></th>
                </tr>
            </thead>
            <tbody id="sessionsTableBody">
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-semibold">{{ $session->subject }}</td>
                    <td>{{ $session->section }}</td>
                    <td>
                        @if($session->session_type === 'afternoon_out')
                            <span class="badge badge-afternoon">🌇</span>
                        @else
                            <span class="badge badge-morning">🌅</span>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell">
                        @if($session->scheduled_start && $session->scheduled_end)
                            <span class="text-muted small">{{ \Carbon\Carbon::parse($session->scheduled_start)->format('h:i A') }} – {{ \Carbon\Carbon::parse($session->scheduled_end)->format('h:i A') }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                        @if($session->camera->is_local_device)<i class="bi bi-laptop text-primary me-1"></i>@endif
                        {{ $session->camera->location }}
                    </td>
                    <td>{{ $session->started_at?->format('M d, h:i A') ?? '—' }}</td>
                    <td class="d-none d-sm-table-cell">{{ $session->ended_at?->format('h:i A') ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $session->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <button onclick="openDrawer('{{ route('teacher.sessions.live', $session) }}','{{ addslashes($session->subject) }} — {{ addslashes($session->section) }} Roster')"
                                class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                        @if($session->status === 'active')
                            <a href="{{ route('teacher.sessions.camera', $session) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-camera-video-fill"></i>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
{{ $sessions->links() }}

{{-- ── Slide-in Drawer ── --}}
<div class="drawer-backdrop" id="drawerBackdrop" onclick="closeDrawer()"></div>
<div class="drawer" id="drawer">
    <div class="drawer-header">
        <i class="bi bi-people-fill text-primary"></i>
        <span class="drawer-title" id="drawerTitle">Session Details</span>
        <button class="drawer-close" onclick="closeDrawer()" title="Close (Esc)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="drawer-body" id="drawerBody">
        <div class="drawer-loading" id="drawerLoading">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span>Loading roster…</span>
        </div>
        <iframe id="drawerFrame" src="about:blank" title="Session roster"></iframe>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Drawer ────────────────────────────────────────────────────────────────────
function openDrawer(url, title) {
    document.getElementById('drawerTitle').textContent = title || 'Session Details';
    document.getElementById('drawerLoading').style.display = 'flex';
    document.getElementById('drawerFrame').style.display   = 'none';
    document.getElementById('drawerFrame').src = 'about:blank';

    document.getElementById('drawerBackdrop').classList.add('open');
    document.getElementById('drawer').classList.add('open');
    document.body.style.overflow = 'hidden';

    // Load the page inside the iframe
    const frame = document.getElementById('drawerFrame');
    frame.onload = function() {
        if (frame.src === 'about:blank') return;
        document.getElementById('drawerLoading').style.display = 'none';
        frame.style.display = 'block';

        // Hide sidebar + topbar inside iframe so it looks clean
        try {
            const iDoc = frame.contentDocument;
            const style = iDoc.createElement('style');
            style.textContent = '.sidebar{display:none!important}.main-content{margin-left:0!important}.top-bar{display:none!important}';
            iDoc.head.appendChild(style);
        } catch(e) {}
    };
    frame.src = url;
}

function closeDrawer() {
    document.getElementById('drawerBackdrop').classList.remove('open');
    document.getElementById('drawer').classList.remove('open');
    document.body.style.overflow = '';
    // Small delay then clear iframe to stop any auto-refresh inside
    setTimeout(() => {
        document.getElementById('drawerFrame').src = 'about:blank';
    }, 350);
}

// Close with Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDrawer();
});

// ── Camera change ─────────────────────────────────────────────────────────────
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

    // ── Auto-select session type based on current time ──────────────────────
    autoSelectSessionType();
    // Re-evaluate every minute in case the page stays open across noon
    setInterval(autoSelectSessionType, 60000);
});

function autoSelectSessionType() {
    const hour       = new Date().getHours();
    const isAfternoon = hour >= 12;   // 12:00 PM and later = afternoon

    const morningRadio   = document.getElementById('stMorning');
    const afternoonRadio = document.getElementById('stAfternoon');
    const morningLabel   = document.getElementById('stMorningLabel');
    const afternoonLabel = document.getElementById('stAfternoonLabel');
    const hint           = document.getElementById('sessionTypeHint');

    if (!morningRadio) return;

    if (isAfternoon) {
        // Disable morning, force afternoon
        morningRadio.disabled = true;
        morningLabel.classList.add('disabled');
        morningLabel.title = 'Morning sessions are only available before 12:00 PM';

        afternoonRadio.disabled = false;
        afternoonLabel.classList.remove('disabled');

        // Auto-select afternoon if morning was selected
        if (morningRadio.checked) {
            afternoonRadio.checked = true;
        }

        hint.innerHTML = '<i class="bi bi-clock text-warning me-1"></i>'
            + '<span style="color:#d97706;">Afternoon — Morning disabled.</span>';
    } else {
        // Morning time — both available, morning pre-selected by default
        morningRadio.disabled = false;
        morningLabel.classList.remove('disabled');
        morningLabel.title = '';

        afternoonRadio.disabled = false;
        afternoonLabel.classList.remove('disabled');

        // Pre-select morning if nothing chosen yet
        if (!morningRadio.checked && !afternoonRadio.checked) {
            morningRadio.checked = true;
        }

        hint.innerHTML = '<i class="bi bi-clock text-success me-1"></i>'
            + '<span style="color:#16a34a;">Morning available.</span>';
    }
}

// ── Schedule input live hint ─────────────────────────────────────────────────
function onScheduleInput() {
    const start = document.getElementById('schedStart').value;
    const end   = document.getElementById('schedEnd').value;
    const hint  = document.getElementById('scheduleHint');
    const startEl = document.getElementById('schedStart');
    const endEl   = document.getElementById('schedEnd');

    startEl.classList.remove('is-invalid', 'is-valid');
    endEl.classList.remove('is-invalid', 'is-valid');

    if (!start && !end) {
        hint.innerHTML = '';
        return;
    }
    if (start && !end) {
        endEl.classList.add('is-invalid');
        hint.innerHTML = '<span style="color:#dc2626;">⚠ Please also set an end time.</span>';
        return;
    }
    if (!start && end) {
        startEl.classList.add('is-invalid');
        hint.innerHTML = '<span style="color:#dc2626;">⚠ Please also set a start time.</span>';
        return;
    }
    if (start >= end) {
        endEl.classList.add('is-invalid');
        hint.innerHTML = '<span style="color:#dc2626;">⚠ End time must be after start time.</span>';
        return;
    }
    startEl.classList.add('is-valid');
    endEl.classList.add('is-valid');
    hint.innerHTML = '<span style="color:#16a34a;">✓ Session will auto-end at ' + formatTime(end) + '</span>';
}

function formatTime(val) {
    const [h, m] = val.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    return ((h % 12) || 12) + ':' + String(m).padStart(2,'0') + ' ' + ampm;
}

// ── Form submit validation ────────────────────────────────────────────────────
async function validateSessionForm(e) {
    e.preventDefault();
    const start = document.getElementById('schedStart').value;
    const end   = document.getElementById('schedEnd').value;

    // Case 1: only one is filled — block and warn
    if ((start && !end) || (!start && end)) {
        onScheduleInput(); // show inline error
        await showConfirm({
            title:   'Incomplete Schedule',
            message: 'You only filled one time field. Please fill both Start and End times, or leave both empty to skip auto-end.',
            okText:  'OK, I\'ll fix it',
            okType:  'warning',
            icon:    '⏰',
        });
        return false;
    }

    // Case 2: end is not after start
    if (start && end && start >= end) {
        onScheduleInput();
        await showConfirm({
            title:   'Invalid Schedule',
            message: 'End time must be after the start time. Please correct the schedule.',
            okText:  'Fix Schedule',
            okType:  'warning',
            icon:    '⏰',
        });
        return false;
    }

    // Case 3: both empty — warn that session won't auto-end, but allow proceed
    if (!start && !end) {
        const confirmed = await showConfirm({
            title:   'No Schedule Set',
            message: 'You have not set a schedule. This session will NOT automatically end — you will need to end it manually. Continue anyway?',
            okText:  'Start Without Schedule',
            okType:  'primary',
            icon:    'ℹ️',
        });
        if (!confirmed) return false;
    }

    // All good — submit
    document.getElementById('sessionForm').submit();
    return true;
}
const SESSIONS_STATS_URL = '{{ route('teacher.sessions.stats') }}';

function renderSessions(sessions) {
    const tbody = document.getElementById('sessionsTableBody');
    if (!tbody) return;
    if (!sessions.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No sessions yet.</td></tr>';
        return;
    }
    tbody.innerHTML = sessions.map(s => {
        const typeBadge = s.session_type === 'afternoon_out'
            ? '<span class="badge badge-afternoon">🌇</span>'
            : '<span class="badge badge-morning">🌅</span>';
        const schedule = (s.scheduled_start && s.scheduled_end)
            ? `<span class="text-muted small">${s.scheduled_start} – ${s.scheduled_end}</span>`
            : '<span class="text-muted small">—</span>';
        const statusBadge = s.status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Ended</span>';
        const cameraBtn = s.status === 'active' && s.camera_route
            ? `<a href="${s.camera_route}" class="btn btn-sm btn-success"><i class="bi bi-camera-video-fill"></i></a>`
            : '';
        const camIcon = s.is_local ? '<i class="bi bi-laptop text-primary me-1"></i>' : '';
        return `<tr>
            <td class="fw-semibold">${s.subject}</td>
            <td>${s.section}</td>
            <td>${typeBadge}</td>
            <td class="d-none d-lg-table-cell">${schedule}</td>
            <td class="d-none d-md-table-cell">${camIcon}${s.camera}</td>
            <td>${s.started_at}</td>
            <td class="d-none d-sm-table-cell">${s.ended_at}</td>
            <td>${statusBadge}</td>
            <td class="text-nowrap">
                <button onclick="openDrawer('${s.live_route}','${s.subject} — ${s.section} Roster')"
                        class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye-fill"></i>
                </button>
                ${cameraBtn}
            </td>
        </tr>`;
    }).join('');
}

async function pollSessions() {
    try {
        const resp = await fetch(SESSIONS_STATS_URL, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        if (!resp.ok) return;
        const data = await resp.json();
        renderSessions(data.sessions);
        const ind = document.getElementById('sessionsLive');
        if (ind) {
            ind.classList.replace('bg-success', 'bg-warning');
            setTimeout(() => ind.classList.replace('bg-warning', 'bg-success'), 800);
        }
    } catch(e) { console.warn('Sessions poll failed:', e); }
}

// Initial render from server data (no flicker on first load)
@php
$initialSessions = $sessions->map(fn($s) => [
    'id'              => $s->id,
    'subject'         => $s->subject,
    'section'         => $s->section,
    'session_type'    => $s->session_type,
    'status'          => $s->status,
    'started_at'      => $s->started_at?->format('M d, h:i A') ?? '—',
    'ended_at'        => $s->ended_at?->format('h:i A') ?? '—',
    'scheduled_start' => $s->scheduled_start ? \Carbon\Carbon::parse($s->scheduled_start)->format('h:i A') : null,
    'scheduled_end'   => $s->scheduled_end   ? \Carbon\Carbon::parse($s->scheduled_end)->format('h:i A')   : null,
    'camera'          => $s->camera->location,
    'is_local'        => $s->camera->is_local_device,
    'camera_route'    => $s->status === 'active' ? route('teacher.sessions.camera', $s->id) : null,
    'live_route'      => route('teacher.sessions.live', $s->id),
]);
@endphp
renderSessions({!! json_encode($initialSessions) !!});

// Poll every 10 seconds
setInterval(pollSessions, 10000);
</script>
@endpush
