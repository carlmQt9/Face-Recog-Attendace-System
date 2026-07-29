@extends('layouts.app')
@section('title', 'My Sessions')
@section('page-title', 'Class Sessions')

@section('content')

{{-- Start New Session --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-play-circle-fill text-success me-2"></i>Start a New Class Session</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.sessions.start') }}" method="POST" class="row g-3" id="sessionForm">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <input type="text" name="section" class="form-control @error('section') is-invalid @enderror"
                       value="{{ old('section') }}" placeholder="e.g. Grade 7 — A">
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100" id="startBtn">
                    <i class="bi bi-play-fill me-1"></i> Start
                </button>
            </div>

            {{-- Local device camera info box (shown when a local camera is selected) --}}
            <div class="col-12" id="localCamInfo" style="display:none;">
                <div class="alert alert-primary d-flex align-items-start gap-3 mb-0 py-3">
                    <i class="bi bi-laptop fs-4 mt-1"></i>
                    <div>
                        <div class="fw-semibold">Using Your Device Camera</div>
                        <div class="small">Your browser will ask for camera permission when the session starts.
                            You can use your <strong>built-in laptop webcam</strong> or open this page on your
                            <strong>phone/tablet</strong> to use its camera. Both front and rear cameras are supported.
                        </div>
                        {{-- Device selector shown only when browser supports enumeration --}}
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
        </form>
    </div>
</div>

{{-- Session History --}}
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Session History</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
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
                        <td>{{ $session->subject }}</td>
                        <td>{{ $session->section }}</td>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $sessions->links() }}

@push('scripts')
<script>
// ── Detect if a local-device camera is selected ───────────────────────────────
function handleCameraChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const isLocal = opt && opt.dataset.local === '1';
    document.getElementById('localCamInfo').style.display = isLocal ? '' : 'none';
    if (isLocal) enumerateDevices();
}

// ── Enumerate available video input devices ───────────────────────────────────
async function enumerateDevices() {
    if (!navigator.mediaDevices?.enumerateDevices) return;

    try {
        // Request permission first so labels are populated
        const testStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        testStream.getTracks().forEach(t => t.stop());

        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(d => d.kind === 'videoinput');

        if (videoDevices.length < 1) return;

        const picker = document.getElementById('devicePicker');
        const wrap   = document.getElementById('devicePickerWrap');

        // Clear old options (keep the auto one)
        while (picker.options.length > 1) picker.remove(1);

        videoDevices.forEach((dev, i) => {
            const opt = document.createElement('option');
            opt.value = dev.deviceId;
            opt.textContent = dev.label || `Camera ${i + 1}`;
            picker.appendChild(opt);
        });

        // Restore saved choice
        const saved = sessionStorage.getItem('preferredCameraDeviceId');
        if (saved) picker.value = saved;

        wrap.style.display = videoDevices.length > 1 ? '' : 'none';

    } catch (e) {
        // Permission denied or not supported — silent fail, browser default will be used
    }
}

function saveDeviceChoice(deviceId) {
    if (deviceId) {
        sessionStorage.setItem('preferredCameraDeviceId', deviceId);
    } else {
        sessionStorage.removeItem('preferredCameraDeviceId');
    }
}

// Run on load if a camera was pre-selected (e.g. after validation error)
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('cameraSelect');
    if (sel.value) handleCameraChange(sel);
});
</script>
@endpush
@endsection
