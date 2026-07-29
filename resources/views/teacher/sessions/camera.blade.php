@extends('layouts.app')
@section('title', 'Live Camera Scanner')
@section('page-title', 'Live Camera — ' . $session->subject)

@push('styles')
<style>
body { background:#0a0a0f; }

/* ── Camera wrapper ─────────────────────────────────── */
.camera-wrapper {
    position:relative; width:100%; max-width:780px; margin:0 auto;
    border-radius:20px; overflow:hidden; background:#000;
    box-shadow:0 0 60px rgba(79,70,229,.3);
    border:2px solid rgba(255,255,255,.07);
    transition: box-shadow .3s;
}
.camera-wrapper.matched   { box-shadow:0 0 80px rgba(74,222,128,.7); border-color:#4ade80; }
.camera-wrapper.no-match  { box-shadow:0 0 60px rgba(248,113,113,.5); border-color:#f87171; }
.camera-wrapper.cooldown  { box-shadow:0 0 60px rgba(250,204,21,.4); border-color:#facc15; }

#videoFeed   { width:100%; display:block; border-radius:18px; }
#scanCanvas  { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; border-radius:18px; }

/* ── Scan line ─────────────────────────────────────── */
.scan-line {
    position:absolute; left:16px; right:16px; height:2px;
    background:linear-gradient(90deg,transparent,#06b6d4,transparent);
    box-shadow:0 0 10px #06b6d4; animation:scanAnim 2.5s linear infinite;
}
@keyframes scanAnim{0%{top:8%}100%{top:90%}}

/* corner marks */
.corner-mark { position:absolute; width:30px; height:30px; }
.cm-tl{top:14px;left:14px;border-top:3px solid #06b6d4;border-left:3px solid #06b6d4;border-radius:6px 0 0 0}
.cm-tr{top:14px;right:14px;border-top:3px solid #06b6d4;border-right:3px solid #06b6d4;border-radius:0 6px 0 0}
.cm-bl{bottom:14px;left:14px;border-bottom:3px solid #06b6d4;border-left:3px solid #06b6d4;border-radius:0 0 0 6px}
.cm-br{bottom:14px;right:14px;border-bottom:3px solid #06b6d4;border-right:3px solid #06b6d4;border-radius:0 0 6px 0}

/* ── Top bar ───────────────────────────────────────── */
.cam-topbar {
    position:absolute;top:0;left:0;right:0;padding:12px 16px;
    background:linear-gradient(to bottom,rgba(0,0,0,.75),transparent);
    display:flex;align-items:center;justify-content:space-between;
    border-radius:20px 20px 0 0;
}
.live-badge {
    display:flex;align-items:center;gap:7px;font-size:12px;font-weight:700;
    color:#4ade80;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.3);
    border-radius:50px;padding:4px 13px;
}
.live-dot{width:7px;height:7px;background:#4ade80;border-radius:50%;animation:lp 1.2s infinite;}
@keyframes lp{0%,100%{opacity:1}50%{opacity:.2}}

/* ── Bottom bar ────────────────────────────────────── */
.cam-bottombar {
    position:absolute;bottom:0;left:0;right:0;padding:14px 18px;
    background:linear-gradient(to top,rgba(0,0,0,.85),transparent);
    border-radius:0 0 20px 20px;
    display:flex;align-items:center;justify-content:space-between;gap:12px;
}
#statusMsg{font-size:15px;font-weight:700;color:#fff;text-shadow:0 1px 4px rgba(0,0,0,.8);}
#statusMsg.ok     {color:#4ade80;}
#statusMsg.error  {color:#f87171;}
#statusMsg.wait   {color:#facc15;}
#statusMsg.info   {color:#93c5fd;}

/* ── Match popup ───────────────────────────────────── */
#matchPopup {
    position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
    background:rgba(0,0,0,.92);border:2px solid #4ade80;border-radius:20px;
    padding:24px 32px;text-align:center;min-width:260px;
    display:none;z-index:20;
    animation:popIn .35s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn{from{opacity:0;transform:translate(-50%,-50%) scale(.7)}to{opacity:1;transform:translate(-50%,-50%) scale(1)}}
#matchPopup .match-icon{font-size:52px;margin-bottom:8px;}
#matchPopup .match-name{font-size:22px;font-weight:800;color:#fff;margin-bottom:4px;}
#matchPopup .match-sub {font-size:13px;color:#4ade80;margin-bottom:16px;}
#matchPopup .match-timer{
    height:6px;background:rgba(255,255,255,.12);border-radius:50px;overflow:hidden;
}
#matchPopup .match-timer-fill{
    height:100%;background:linear-gradient(90deg,#4ade80,#06b6d4);
    border-radius:50px;width:100%;
    transition:width linear;
}

/* ── No-cam fallback ───────────────────────────────── */
.no-cam {
    aspect-ratio:16/9;display:flex;flex-direction:column;
    align-items:center;justify-content:center;
    color:#475569;font-size:15px;gap:12px;
    background:#0a0a1a;border-radius:18px;
}
.no-cam i{font-size:48px;color:#334155;}

/* ── Roster panel ──────────────────────────────────── */
.roster-panel{
    background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
    border-radius:20px;overflow:hidden;
}
.roster-header{
    background:rgba(255,255,255,.05);padding:14px 18px;
    border-bottom:1px solid rgba(255,255,255,.07);
    display:flex;align-items:center;justify-content:space-between;
}
.roster-header h6{color:#fff;font-weight:700;margin:0;}
#rosterCount{background:rgba(79,70,229,.2);color:#818cf8;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:700;}
#rosterList{max-height:460px;overflow-y:auto;padding:8px;}
.roster-item{
    display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:12px;
    margin-bottom:4px;animation:slideIn .3s ease;
    background:rgba(74,222,128,.05);border:1px solid rgba(74,222,128,.1);
}
@keyframes slideIn{from{opacity:0;transform:translateX(-12px)}to{opacity:1;transform:translateX(0)}}
.roster-avatar{
    width:36px;height:36px;border-radius:10px;
    background:linear-gradient(135deg,#4f46e5,#06b6d4);
    display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;
}
.roster-name{font-size:14px;font-weight:600;color:#fff;}
.roster-time{font-size:12px;color:#64748b;}
.roster-badge{margin-left:auto;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;}
.badge-face  {background:rgba(79,70,229,.2);color:#818cf8;}
.badge-manual{background:rgba(250,204,21,.15);color:#facc15;}
#rosterList::-webkit-scrollbar{width:4px;}
#rosterList::-webkit-scrollbar-thumb{background:#334155;border-radius:2px;}

/* ── Loading state ─────────────────────────────────── */
.loading-overlay{
    position:absolute;inset:0;background:rgba(10,10,15,.9);
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;
    border-radius:18px;z-index:15;
}
.loading-overlay p{color:#94a3b8;font-size:14px;font-weight:600;margin:0;}
.spinner-ring{
    width:50px;height:50px;border:4px solid rgba(255,255,255,.1);
    border-top-color:#4f46e5;border-radius:50%;animation:spin .8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Auto/manual toggle ─────────────────────────────── */
.mode-toggle{
    display:flex;gap:6px;background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:4px;
}
.mode-btn{
    font-size:12px;font-weight:700;border:none;border-radius:9px;
    padding:6px 14px;cursor:pointer;transition:all .2s;color:rgba(255,255,255,.5);background:transparent;
}
.mode-btn.active{background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;}

.scan-btn{
    background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;
    border:none;border-radius:12px;padding:9px 22px;font-size:13px;font-weight:700;
    cursor:pointer;transition:opacity .15s,transform .15s;
    display:flex;align-items:center;gap:7px;
}
.scan-btn:hover{opacity:.85;transform:translateY(-1px);}
.scan-btn:disabled{opacity:.35;cursor:not-allowed;}

/* ── Camera switch ─────────────────────────────────── */
.cam-switch-btn{
    background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.12);
    color:#fff;border-radius:10px;padding:7px 13px;font-size:13px;
    cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;
}
.cam-switch-btn:hover{background:rgba(255,255,255,.16);}

/* ── Confidence bar ────────────────────────────────── */
.conf-bar-wrap{height:4px;background:rgba(255,255,255,.08);border-radius:50px;overflow:hidden;width:80px;}
.conf-bar{height:100%;border-radius:50px;background:#4ade80;transition:width .3s;}

/* ── Device picker ─────────────────────────────────── */
.device-bar{
    background:rgba(15,15,30,.9);border:1px solid rgba(255,255,255,.08);
    border-radius:13px;padding:10px 14px;margin-bottom:12px;
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.device-bar label{color:rgba(255,255,255,.6);font-size:12px;white-space:nowrap;margin:0;}
.device-bar select{
    background:#1e293b;border:1px solid rgba(255,255,255,.12);
    color:#fff;border-radius:8px;padding:5px 10px;font-size:12px;flex:1;min-width:160px;
}
</style>
@endpush

@section('content')
<div class="row g-4">

    {{-- ── LEFT: Camera Feed ── --}}
    <div class="col-lg-7">

        {{-- Top bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 text-white fw-bold">{{ $session->subject }}</h5>
                <small class="text-secondary">
                    {{ $session->section }} &nbsp;·&nbsp; {{ $session->camera->location }}
                    @if($session->camera->is_local_device)
                        &nbsp;<span class="badge bg-primary"><i class="bi bi-laptop me-1"></i>Local Device</span>
                    @endif
                </small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if($session->camera->is_local_device)
                <button class="cam-switch-btn" onclick="switchCamera()" title="Toggle front/rear">
                    <i class="bi bi-arrow-repeat"></i> Switch
                </button>
                @endif
                <div class="mode-toggle">
                    <button class="mode-btn active" id="modeAuto" onclick="setMode('auto')">
                        <i class="bi bi-magic me-1"></i>Auto
                    </button>
                    <button class="mode-btn" id="modeManual" onclick="setMode('manual')">
                        <i class="bi bi-hand-index me-1"></i>Manual
                    </button>
                </div>
                @if($session->isActive())
                <form action="{{ route('teacher.sessions.stop', $session) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-danger" style="border-radius:10px;padding:7px 14px;"
                            onclick="return confirm('End this session?')">
                        <i class="bi bi-stop-circle-fill me-1"></i>End
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Device picker --}}
        @if($session->camera->is_local_device)
        <div class="device-bar" id="deviceBar" style="display:none;">
            <i class="bi bi-camera-video text-primary"></i>
            <label>Camera:</label>
            <select id="deviceSelect" onchange="switchToDevice(this.value)">
                <option value="">— Auto —</option>
            </select>
        </div>
        @endif

        {{-- Camera box --}}
        <div class="camera-wrapper" id="cameraWrapper">
            <video id="videoFeed" autoplay playsinline muted></video>
            <canvas id="scanCanvas"></canvas>

            {{-- Scan overlay --}}
            <div class="scan-line"></div>
            <div class="corner-mark cm-tl"></div>
            <div class="corner-mark cm-tr"></div>
            <div class="corner-mark cm-bl"></div>
            <div class="corner-mark cm-br"></div>

            {{-- Top bar --}}
            <div class="cam-topbar">
                <div class="live-badge"><span class="live-dot"></span>LIVE</div>
                <div class="d-flex align-items-center gap-10" style="gap:10px;">
                    <div class="conf-bar-wrap" id="confWrap" title="Confidence" style="display:none;">
                        <div class="conf-bar" id="confBar" style="width:0%"></div>
                    </div>
                    <span style="font-size:12px;color:rgba(255,255,255,.5);" id="camLabel">Starting…</span>
                </div>
            </div>

            {{-- Match popup --}}
            <div id="matchPopup">
                <div class="match-icon">✅</div>
                <div class="match-name" id="matchName">—</div>
                <div class="match-sub" id="matchSub">Marked Present</div>
                <div class="match-timer"><div class="match-timer-fill" id="timerFill"></div></div>
            </div>

            {{-- Bottom bar --}}
            <div class="cam-bottombar">
                <div id="statusMsg" class="info">Loading face data…</div>
                @if($session->isActive())
                <button class="scan-btn" id="scanBtn" onclick="manualScan()" style="display:none;" disabled>
                    <i class="bi bi-person-bounding-box"></i> Scan Now
                </button>
                @endif
            </div>

            {{-- Loading overlay --}}
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner-ring"></div>
                <p id="loadingText">Initialising face recognition…</p>
            </div>

            {{-- No cam fallback --}}
            <div class="no-cam" id="noCamMsg" style="display:none;">
                <i class="bi bi-camera-video-off"></i>
                <span id="noCamText">Camera not accessible</span>
                <small id="noCamHint">Allow camera permission then click Retry</small>
                <button class="btn btn-sm btn-primary mt-2" onclick="startCamera()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Retry
                </button>
            </div>
        </div>

        {{-- Signal guide --}}
        <div class="d-flex gap-3 mt-3">
            <div class="flex-fill text-center p-3" style="background:rgba(74,222,128,.07);border:1px solid rgba(74,222,128,.2);border-radius:14px;">
                <div style="font-size:18px;">🔊</div>
                <div style="font-size:12px;font-weight:700;color:#4ade80;">1 Beep — Match</div>
                <div style="font-size:11px;color:#475569;">Student marked present</div>
            </div>
            <div class="flex-fill text-center p-3" style="background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.2);border-radius:14px;">
                <div style="font-size:18px;">🔊🔊</div>
                <div style="font-size:12px;font-weight:700;color:#f87171;">2 Beeps — No Match</div>
                <div style="font-size:11px;color:#475569;">Face not recognized</div>
            </div>
            <div class="flex-fill text-center p-3" style="background:rgba(250,204,21,.07);border:1px solid rgba(250,204,21,.2);border-radius:14px;">
                <div style="font-size:18px;">⏳</div>
                <div style="font-size:12px;font-weight:700;color:#facc15;" id="nextInLabel">Next in 3s</div>
                <div style="font-size:11px;color:#475569;">Auto cooldown</div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Live Roster ── --}}
    <div class="col-lg-5">
        <div class="roster-panel">
            <div class="roster-header">
                <h6><i class="bi bi-people-fill me-2 text-primary"></i>Live Roster</h6>
                <span id="rosterCount">0</span>
            </div>

            @if($session->isActive())
            <div class="p-3" style="border-bottom:1px solid rgba(255,255,255,.07);">
                <form action="{{ route('teacher.sessions.manual-attend', $session) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <select name="student_id" class="form-select form-select-sm"
                            style="background:#1e293b;border-color:rgba(255,255,255,.1);color:#fff;">
                        <option value="">— Manual mark student —</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-warning" style="border-radius:8px;white-space:nowrap;">
                        <i class="bi bi-hand-index-fill"></i> Mark
                    </button>
                </form>
            </div>
            @endif

            <div id="rosterList">
                @forelse($attendance as $record)
                <div class="roster-item">
                    <div class="roster-avatar">👤</div>
                    <div>
                        <div class="roster-name">{{ $record->student->user->name }}</div>
                        <div class="roster-time">{{ $record->arrived_at->format('h:i A') }}</div>
                    </div>
                    <span class="roster-badge {{ $record->method==='manual'?'badge-manual':'badge-face' }}">
                        {{ $record->method==='manual'?'Manual':'Face' }}
                    </span>
                </div>
                @empty
                <div class="text-center py-5" style="color:#334155;" id="emptyRoster">
                    <i class="bi bi-camera" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                    Waiting for first scan…
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<audio id="successAudio" preload="auto"><source src="/sounds/success.mp3" type="audio/mpeg"></audio>
<audio id="errorAudio"   preload="auto"><source src="/sounds/error.mp3"   type="audio/mpeg"></audio>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script>
'use strict';
// ═══════════════════════════════════════════════════════
//  CONFIG
// ═══════════════════════════════════════════════════════
const MODELS_URL        = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
const MATCH_THRESHOLD   = 0.50;   // lower = stricter (0.6 is face-api default; 0.50 reduces false positives)
const COOLDOWN_SECS     = {{ \App\Models\SystemSetting::get('cooldown_seconds', 4) }};
const NEXT_PERSON_SECS  = 4;      // seconds to show match popup before resuming scan
const SCAN_INTERVAL_MS  = 800;    // auto-scan every 800ms
const IS_LOCAL          = {{ $session->camera->is_local_device ? 'true' : 'false' }};
const SESSION_ID        = {{ $session->id }};
const CAMERA_ID         = {{ $session->camera_id }};
const IS_ACTIVE         = {{ $session->isActive() ? 'true' : 'false' }};

// ═══════════════════════════════════════════════════════
//  STATE
// ═══════════════════════════════════════════════════════
let stream              = null;
let facingMode          = 'user';
let preferredDeviceId   = sessionStorage.getItem('preferredCamId') || null;
let faceMatcher         = null;   // faceapi.FaceMatcher built from registered students
let studentMap          = {};     // studentId -> { name, student_id }
let modelsLoaded        = false;
let autoScanInterval    = null;
let scanMode            = 'auto'; // 'auto' | 'manual'
let inCooldown          = false;
let rosterCount         = {{ $attendance->count() }};
// Track which students are already marked in this session (client-side dedup)
let markedIds           = new Set([
    @foreach($attendance as $r) {{ $r->student_id }}, @endforeach
]);

// DOM refs
const video       = document.getElementById('videoFeed');
const canvas      = document.getElementById('scanCanvas');
const overlay     = document.getElementById('loadingOverlay');
const loadingText = document.getElementById('loadingText');
const statusMsg   = document.getElementById('statusMsg');
const scanBtn     = document.getElementById('scanBtn');
const wrapper     = document.getElementById('cameraWrapper');
const noCamMsg    = document.getElementById('noCamMsg');
const matchPopup  = document.getElementById('matchPopup');
const confBar     = document.getElementById('confBar');
const confWrap    = document.getElementById('confWrap');

// ═══════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════
window.addEventListener('DOMContentLoaded', async () => {
    setLoading('Starting camera…');
    await startCamera();

    setLoading('Loading face recognition models…');
    await loadModels();

    setLoading('Loading registered face data…');
    await buildMatcher();

    hideLoading();

    if (scanMode === 'auto' && IS_ACTIVE) {
        startAutoScan();
        setStatus('🔍 Auto-scanning — stand in front of the camera', 'info');
    } else {
        setStatus('Ready — press Scan Now', 'info');
        if (scanBtn) { scanBtn.style.display = 'flex'; scanBtn.disabled = false; }
    }
});

// ═══════════════════════════════════════════════════════
//  MODELS
// ═══════════════════════════════════════════════════════
async function loadModels() {
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL);
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL);
        modelsLoaded = true;
    } catch (e) {
        console.warn('Models load failed:', e);
        modelsLoaded = false;
        setStatus('⚠️ Face recognition unavailable — use Manual mode', 'error');
    }
}

// ═══════════════════════════════════════════════════════
//  BUILD FACE MATCHER from registered student images
// ═══════════════════════════════════════════════════════
async function buildMatcher() {
    if (!modelsLoaded) return;
    try {
        const resp = await fetch('/api/face-descriptors', { headers: { Accept: 'application/json' } });
        const data = await resp.json();

        if (!data.students || data.students.length === 0) {
            setStatus('⚠️ No registered faces found. Register faces in Admin → Face Registration.', 'error');
            return;
        }

        const labeledDescriptors = [];

        for (const s of data.students) {
            studentMap[s.student_id] = { name: s.student_name, code: s.student_code };
            const descriptors = [];

            for (const imgUrl of s.face_images) {
                try {
                    const img = await loadImage(imgUrl);
                    const det = await faceapi
                        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 }))
                        .withFaceLandmarks(true)
                        .withFaceDescriptor();
                    if (det) descriptors.push(det.descriptor);
                } catch (e) {
                    console.warn('Could not process image for', s.student_name, imgUrl, e);
                }
            }

            if (descriptors.length > 0) {
                labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(
                    String(s.student_id),
                    descriptors
                ));
            }
        }

        if (labeledDescriptors.length === 0) {
            setStatus('⚠️ Could not extract descriptors. Re-register faces with good lighting.', 'error');
            return;
        }

        faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, MATCH_THRESHOLD);
        console.log(`✅ Face matcher built with ${labeledDescriptors.length} student(s).`);

    } catch (e) {
        console.error('buildMatcher error:', e);
        setStatus('⚠️ Failed to load face data — ' + e.message, 'error');
    }
}

// Helper: load an image element from URL (handles CORS)
function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload  = () => resolve(img);
        img.onerror = (e) => reject(e);
        img.src = src;
    });
}

// ═══════════════════════════════════════════════════════
//  CAMERA
// ═══════════════════════════════════════════════════════
async function startCamera() {
    try {
        if (stream) stream.getTracks().forEach(t => t.stop());
        noCamMsg.style.display = 'none';
        video.style.display    = 'block';

        const vc = {};
        if (IS_LOCAL) {
            if (preferredDeviceId) {
                vc.deviceId = { exact: preferredDeviceId };
                video.style.transform = 'scaleX(-1)';
            } else {
                vc.facingMode = facingMode;
                video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }
        vc.width  = { ideal: 1280 };
        vc.height = { ideal: 720 };

        stream = await navigator.mediaDevices.getUserMedia({ video: vc, audio: false });
        video.srcObject = stream;
        await new Promise(r => video.addEventListener('loadeddata', r, { once: true }));

        const track    = stream.getVideoTracks()[0];
        const settings = track.getSettings();
        document.getElementById('camLabel').textContent =
            (track.label || (facingMode === 'user' ? 'Front Camera' : 'Rear Camera')) +
            (settings.width ? ' · ' + settings.width + 'px' : '');

        if (IS_LOCAL) populateDevicePicker();

    } catch (err) {
        video.style.display    = 'none';
        noCamMsg.style.display = 'flex';
        document.getElementById('noCamText').textContent =
            err.name === 'NotAllowedError' ? 'Camera permission denied' :
            err.name === 'NotFoundError'   ? 'No camera found' : 'Camera not accessible';
        document.getElementById('noCamHint').textContent =
            err.name === 'NotAllowedError'
                ? 'Open browser settings, allow camera access, then click Retry.'
                : 'Connect a camera or use a different device.';
        document.getElementById('camLabel').textContent = 'Camera unavailable';
        hideLoading();
    }
}

async function populateDevicePicker() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    const devs  = await navigator.mediaDevices.enumerateDevices();
    const vdevs = devs.filter(d => d.kind === 'videoinput');
    if (vdevs.length < 2) return;

    const sel = document.getElementById('deviceSelect');
    const bar = document.getElementById('deviceBar');
    if (!sel || !bar) return;
    while (sel.options.length > 1) sel.remove(1);
    vdevs.forEach((d, i) => {
        const o = document.createElement('option');
        o.value = d.deviceId;
        o.textContent = d.label || `Camera ${i+1}`;
        sel.appendChild(o);
    });
    if (preferredDeviceId) sel.value = preferredDeviceId;
    bar.style.display = '';
}

function switchCamera() {
    preferredDeviceId = null;
    sessionStorage.removeItem('preferredCamId');
    facingMode = facingMode === 'user' ? 'environment' : 'user';
    video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
    startCamera();
}

function switchToDevice(deviceId) {
    preferredDeviceId = deviceId || null;
    deviceId ? sessionStorage.setItem('preferredCamId', deviceId) : sessionStorage.removeItem('preferredCamId');
    startCamera();
}

// ═══════════════════════════════════════════════════════
//  SCAN MODE
// ═══════════════════════════════════════════════════════
function setMode(mode) {
    scanMode = mode;
    document.getElementById('modeAuto').classList.toggle('active', mode === 'auto');
    document.getElementById('modeManual').classList.toggle('active', mode === 'manual');

    if (mode === 'auto') {
        if (scanBtn) scanBtn.style.display = 'none';
        if (!inCooldown && IS_ACTIVE) startAutoScan();
        setStatus('🔍 Auto-scanning — stand in front of the camera', 'info');
    } else {
        stopAutoScan();
        if (scanBtn) { scanBtn.style.display = 'flex'; scanBtn.disabled = false; }
        setStatus('Manual mode — press Scan Now', 'info');
    }
}

// ═══════════════════════════════════════════════════════
//  AUTO-SCAN LOOP
// ═══════════════════════════════════════════════════════
function startAutoScan() {
    stopAutoScan();
    autoScanInterval = setInterval(runScan, SCAN_INTERVAL_MS);
}

function stopAutoScan() {
    if (autoScanInterval) { clearInterval(autoScanInterval); autoScanInterval = null; }
}

function manualScan() {
    if (inCooldown) return;
    runScan();
}

// ═══════════════════════════════════════════════════════
//  CORE SCAN — detect face → match → record attendance
// ═══════════════════════════════════════════════════════
async function runScan() {
    if (inCooldown || !stream || !video.videoWidth) return;
    if (!modelsLoaded || !faceMatcher) {
        setStatus('⚠️ Face matcher not ready', 'error');
        return;
    }

    try {
        const detection = await faceapi
            .detectSingleFace(video,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
            .withFaceLandmarks(true)
            .withFaceDescriptor();

        if (!detection) {
            drawNoFace();
            setStatus('🔍 No face detected — look at the camera', 'info');
            updateConfidence(0);
            return;
        }

        // Match against registered faces
        const match      = faceMatcher.findBestMatch(detection.descriptor);
        const studentId  = match.label === 'unknown' ? null : parseInt(match.label, 10);
        const confidence = Math.round((1 - match.distance) * 100);   // 0-100%
        const info       = studentId ? studentMap[studentId] : null;

        drawFaceBox(detection.detection.box, studentId !== null, info?.name, confidence);
        updateConfidence(studentId ? confidence : 0);

        if (!studentId || match.distance > MATCH_THRESHOLD) {
            setStatus('❌ Face not recognized — adjust lighting or position', 'error');
            wrapper.className = 'camera-wrapper no-match';
            playBeep('error');
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 1000);
            return;
        }

        // Already marked this session?
        if (markedIds.has(studentId)) {
            setStatus(`ℹ️ ${info.name} already marked present`, 'wait');
            return;
        }

        // ── Record attendance ──────────────────────────────────
        stopAutoScan();
        inCooldown = true;
        if (scanBtn) scanBtn.disabled = true;

        const frame = captureFrame();
        await recordAttendance(studentId, confidence, frame);

    } catch (e) {
        console.error('runScan error:', e);
    }
}

// ═══════════════════════════════════════════════════════
//  RECORD ATTENDANCE via server API
// ═══════════════════════════════════════════════════════
async function recordAttendance(studentId, confidence, frame) {
    try {
        const resp = await fetch('/api/face-scan', {
            method:  'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                'Accept':        'application/json',
            },
            body: JSON.stringify({
                camera_id:        CAMERA_ID,
                session_id:       SESSION_ID,
                student_id:       studentId,
                scan_result:      'success',
                confidence_score: confidence,
                face_image:       frame,
            }),
        });

        const data = await resp.json();

        if (data.result === 'success') {
            markedIds.add(studentId);
            playBeep('success');
            wrapper.className = 'camera-wrapper matched';
            addToRoster(data.student_name, 'face', data.arrived_at);
            showMatchPopup(data.student_name, `${confidence}% match · ${data.arrived_at}`);
            setStatus(`✅ ${data.student_name} — Present`, 'ok');
            await nextPersonCooldown(NEXT_PERSON_SECS);

        } else if (data.result === 'cooldown') {
            setStatus('⏳ Already scanned recently', 'wait');
            wrapper.className = 'camera-wrapper cooldown';
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 1500);
            resumeAfter(2);

        } else {
            setStatus('❌ Server error recording attendance', 'error');
            resumeAfter(2);
        }

    } catch (e) {
        setStatus('⚠️ Network error — ' + e.message, 'error');
        resumeAfter(3);
    }
}

// ═══════════════════════════════════════════════════════
//  MATCH POPUP + COUNTDOWN
// ═══════════════════════════════════════════════════════
function showMatchPopup(name, sub) {
    document.getElementById('matchName').textContent = name;
    document.getElementById('matchSub').textContent  = sub;
    matchPopup.style.display = 'block';
}

function hideMatchPopup() {
    matchPopup.style.display = 'none';
    wrapper.className = 'camera-wrapper';
}

async function nextPersonCooldown(secs) {
    const fill = document.getElementById('timerFill');
    fill.style.transition = 'none';
    fill.style.width = '100%';

    document.getElementById('nextInLabel').textContent = `Next in ${secs}s`;

    // Animate bar shrinking
    await new Promise(r => setTimeout(r, 50));
    fill.style.transition = `width ${secs}s linear`;
    fill.style.width = '0%';

    let remaining = secs;
    const tick = setInterval(() => {
        remaining--;
        document.getElementById('nextInLabel').textContent =
            remaining > 0 ? `Next in ${remaining}s` : 'Ready';
    }, 1000);

    await new Promise(r => setTimeout(r, secs * 1000));
    clearInterval(tick);
    hideMatchPopup();
    resumeAfter(0);
}

function resumeAfter(secs) {
    setTimeout(() => {
        inCooldown = false;
        wrapper.className = 'camera-wrapper';
        if (scanMode === 'auto' && IS_ACTIVE) {
            startAutoScan();
            setStatus('🔍 Auto-scanning — next person please', 'info');
        } else {
            if (scanBtn) scanBtn.disabled = false;
            setStatus('Ready — press Scan Now', 'info');
        }
    }, secs * 1000);
}

// ═══════════════════════════════════════════════════════
//  CANVAS DRAWING
// ═══════════════════════════════════════════════════════
function drawFaceBox(box, matched, name, confidence) {
    canvas.width  = video.clientWidth;
    canvas.height = video.clientHeight;
    const ctx  = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const scaleX  = canvas.width  / video.videoWidth;
    const scaleY  = canvas.height / video.videoHeight;
    const mirrorX = canvas.width - (box.x + box.width) * scaleX;
    const bx = mirrorX;
    const by = box.y    * scaleY;
    const bw = box.width  * scaleX;
    const bh = box.height * scaleY;

    const color = matched ? '#4ade80' : '#f87171';
    ctx.strokeStyle = color;
    ctx.lineWidth   = 3;
    ctx.shadowColor = color;
    ctx.shadowBlur  = 12;
    ctx.strokeRect(bx, by, bw, bh);

    // Label
    if (name || !matched) {
        const label = matched ? `${name}  ${confidence}%` : 'Unknown';
        ctx.font      = 'bold 14px Inter, sans-serif';
        ctx.fillStyle = 'rgba(0,0,0,.65)';
        ctx.fillRect(bx, by - 26, ctx.measureText(label).width + 16, 24);
        ctx.fillStyle = color;
        ctx.shadowBlur = 0;
        ctx.fillText(label, bx + 8, by - 8);
    }
}

function drawNoFace() {
    canvas.width  = video.clientWidth;
    canvas.height = video.clientHeight;
    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
}

function captureFrame() {
    const c   = document.createElement('canvas');
    c.width   = video.videoWidth;
    c.height  = video.videoHeight;
    const ctx = c.getContext('2d');
    ctx.translate(c.width, 0); ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0);
    return c.toDataURL('image/jpeg', 0.85);
}

function updateConfidence(pct) {
    confWrap.style.display = pct > 0 ? '' : 'none';
    confBar.style.width    = pct + '%';
    confBar.style.background = pct >= 70 ? '#4ade80' : pct >= 50 ? '#facc15' : '#f87171';
}

// ═══════════════════════════════════════════════════════
//  ROSTER
// ═══════════════════════════════════════════════════════
function addToRoster(name, method, time) {
    document.getElementById('emptyRoster')?.remove();
    rosterCount++;
    document.getElementById('rosterCount').textContent = rosterCount;
    const item = document.createElement('div');
    item.className = 'roster-item';
    item.innerHTML = `
        <div class="roster-avatar">👤</div>
        <div><div class="roster-name">${name}</div><div class="roster-time">${time}</div></div>
        <span class="roster-badge ${method==='manual'?'badge-manual':'badge-face'}">
            ${method==='manual'?'Manual':'Face'}</span>`;
    document.getElementById('rosterList').prepend(item);
}

// ═══════════════════════════════════════════════════════
//  BEEP
// ═══════════════════════════════════════════════════════
function playBeep(type) {
    const el = document.getElementById(type === 'success' ? 'successAudio' : 'errorAudio');
    if (el?.src && !el.src.endsWith('/')) { el.currentTime = 0; el.play().catch(() => {}); return; }
    const ac = new (window.AudioContext || window.webkitAudioContext)();
    const count = type === 'success' ? 1 : 2;
    for (let i = 0; i < count; i++) {
        const osc = ac.createOscillator(), gain = ac.createGain();
        osc.connect(gain); gain.connect(ac.destination);
        osc.frequency.value = type === 'success' ? 880 : 440; osc.type = 'sine';
        gain.gain.setValueAtTime(0.3, ac.currentTime + i * 0.35);
        gain.gain.exponentialRampToValueAtTime(0.001, ac.currentTime + i * 0.35 + 0.25);
        osc.start(ac.currentTime + i * 0.35);
        osc.stop(ac.currentTime + i * 0.35 + 0.3);
    }
}

// ═══════════════════════════════════════════════════════
//  UI HELPERS
// ═══════════════════════════════════════════════════════
function setStatus(msg, cls) {
    statusMsg.textContent = msg;
    statusMsg.className   = cls;
}
function setLoading(msg) {
    overlay.style.display = 'flex';
    loadingText.textContent = msg;
}
function hideLoading() {
    overlay.style.display = 'none';
}

// Spacebar manual scan
document.addEventListener('keydown', e => {
    if (e.code === 'Space' && !e.target.matches('input,select,textarea')) {
        e.preventDefault();
        if (scanMode === 'manual' && !inCooldown) manualScan();
    }
});
</script>
@endpush
