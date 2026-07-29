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
        <form action="{{ route('teacher.sessions.start') }}" method="POST" id="sessionForm">
            @csrf
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <input type="text" name="section"
                           class="form-control @error('section') is-invalid @enderror"
                           value="{{ old('section') }}" placeholder="e.g. Grade 7-A">
                    @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

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

                <div class="col-md-3">
                    <label class="form-label">
                        Session Type
                        @error('session_type')<span class="text-danger small"> {{ $message }}</span>@enderror
                    </label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="stype-radio" name="session_type"
                               id="stMorning" value="morning_in"
                               {{ old('session_type','morning_in') === 'morning_in' ? 'checked' : '' }}>
                        <label for="stMorning" class="stype-pill">🌅 Morning In</label>

                        <input type="radio" class="stype-radio out" name="session_type"
                               id="stAfternoon" value="afternoon_out"
                               {{ old('session_type') === 'afternoon_out' ? 'checked' : '' }}>
                        <label for="stAfternoon" class="stype-pill">🌇 Afternoon Out</label>
                    </div>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-play-fill"></i> Start
                    </button>
                </div>
            </div>

            <div id="localCamInfo" style="display:none;" class="mt-3">
                <div class="alert alert-primary d-flex align-items-start gap-3 mb-0 py-2">
                    <i class="bi bi-laptop fs-5 mt-1 flex-shrink-0"></i>
                    <div class="small">
                        <strong>Using Your Device Camera</strong> — your browser will request
                        permission when the session starts.
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
                    <th>Subject</th><th>Section</th><th>Type</th><th>Camera</th>
                    <th>Started</th><th>Ended</th><th>Status</th><th></th>
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
                        {{-- View opens in drawer --}}
                        <button onclick="openDrawer(
                                    '{{ route('teacher.sessions.live', $session) }}',
                                    '{{ addslashes($session->subject) }} — {{ addslashes($session->section) }} Roster'
                                 )"
                                class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye-fill"></i> View
                        </button>
                        @if($session->status === 'active')
                            {{-- Camera goes to full page (needs full camera access) --}}
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
});
</script>
@endpush
