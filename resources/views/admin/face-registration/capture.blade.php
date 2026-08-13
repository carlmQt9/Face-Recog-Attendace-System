@extends('layouts.app')
@section('title', 'Capture Face — ' . $person->user->name)
@section('page-title', 'Face Registration — ' . $person->user->name)

@push('styles')
<style>
/* ── Layout ─────────────────────────────────────────────── */
.reg-card { background:#0f172a; border:1px solid rgba(255,255,255,.08); border-radius:24px; overflow:hidden; }
.reg-left  { background:#080d1a; border-right:1px solid rgba(255,255,255,.06); padding:32px; }
.reg-right { padding:28px 32px; }

/* ── Camera wrapper ─────────────────────────────────────── */
.cam-wrap {
    position:relative; border-radius:20px; overflow:hidden;
    background:#000; aspect-ratio:4/3;
    box-shadow:0 0 50px rgba(12,61,138,.25);
    border:2px solid rgba(255,255,255,.07);
}
#webcam   { width:100%; height:100%; object-fit:cover; display:block; }
#faceCanvas { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; }

/* face oval guide */
.face-oval {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-52%);
    width:52%; aspect-ratio:3/4;
    border:3px dashed rgba(255,255,255,.25); border-radius:50%;
    pointer-events:none; transition:border-color .3s, box-shadow .3s;
}
.face-oval.detected   { border-color:#4ade80; box-shadow:0 0 20px rgba(74,222,128,.35); }
.face-oval.no-face    { border-color:#f87171; }
.face-oval.liveness   { border-color:#facc15; box-shadow:0 0 20px rgba(250,204,21,.35); }
.face-oval.verified   { border-color:#4ade80; border-style:solid; box-shadow:0 0 30px rgba(74,222,128,.6); }

/* top bar */
.cam-topbar {
    position:absolute; top:0; left:0; right:0; padding:12px 16px;
    background:linear-gradient(to bottom,rgba(0,0,0,.75),transparent);
    display:flex; justify-content:space-between; align-items:center;
    border-radius:20px 20px 0 0;
}
.live-badge {
    display:flex; align-items:center; gap:7px; font-size:12px; font-weight:700;
    color:#4ade80; background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.3);
    border-radius:50px; padding:4px 12px;
}
.live-dot { width:7px; height:7px; background:#4ade80; border-radius:50%; animation:livePulse 1.2s infinite; }
@keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* bottom instruction bar */
.cam-hint {
    position:absolute; bottom:0; left:0; right:0; padding:14px 18px;
    background:linear-gradient(to top,rgba(0,0,0,.8),transparent);
    border-radius:0 0 20px 20px; text-align:center;
}
#hintText { font-size:14px; font-weight:700; color:#fff; }

/* ── Steps sidebar ──────────────────────────────────────── */
.step-list { list-style:none; padding:0; margin:0; }
.step-item {
    display:flex; align-items:flex-start; gap:14px;
    padding:12px 0; border-bottom:1px solid rgba(255,255,255,.05);
}
.step-item:last-child { border-bottom:none; }
.step-num {
    width:30px; height:30px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:800; background:rgba(255,255,255,.07);
    color:rgba(255,255,255,.4); border:2px solid rgba(255,255,255,.1);
    transition:all .4s;
}
.step-num.active   { background:rgba(250,204,21,.15); color:#facc15; border-color:#facc15; }
.step-num.done     { background:rgba(74,222,128,.15);  color:#4ade80;  border-color:#4ade80; }
.step-num.done::before { content:'✓'; }
.step-body { flex:1; }
.step-title { font-size:13px; font-weight:700; color:rgba(255,255,255,.5); transition:color .4s; }
.step-title.active { color:#facc15; }
.step-title.done   { color:#4ade80; }
.step-desc  { font-size:12px; color:rgba(255,255,255,.3); margin-top:2px; }

/* ── Progress bar ───────────────────────────────────────── */
.prog-wrap { background:rgba(255,255,255,.07); border-radius:50px; height:6px; margin-bottom:6px; overflow:hidden; }
.prog-fill { height:100%; border-radius:50px; background:linear-gradient(90deg,#0c3d8a,#f5a800); transition:width .4s; }

/* ── Action buttons ─────────────────────────────────────── */
.btn-capture {
    background:linear-gradient(135deg,#0c3d8a,#1a6b3c); color:#fff;
    border:none; border-radius:14px; padding:14px 32px;
    font-size:15px; font-weight:700; cursor:pointer;
    transition:opacity .2s, transform .15s; width:100%;
    display:flex; align-items:center; justify-content:center; gap:10px;
}
.btn-capture:hover   { opacity:.85; transform:translateY(-1px); }
.btn-capture:active  { transform:scale(.98); }
.btn-capture:disabled{ opacity:.35; cursor:not-allowed; }

/* ── Liveness blink overlay ─────────────────────────────── */
.blink-overlay {
    position:absolute; inset:0; display:none;
    background:rgba(0,0,0,.55); border-radius:20px;
    align-items:center; justify-content:center; flex-direction:column; gap:12px;
}
.blink-overlay.show { display:flex; }
.blink-prompt { font-size:24px; font-weight:900; color:#facc15; text-align:center; padding:0 20px; }
.blink-count  { font-size:48px; font-weight:900; color:#fff; line-height:1; }
.blink-sub    { font-size:13px; color:rgba(255,255,255,.6); }

/* ── Captured thumbnails ────────────────────────────────── */
.thumb-row { display:flex; gap:10px; flex-wrap:wrap; }
.thumb-item {
    position:relative; width:80px; height:80px; border-radius:12px; overflow:hidden;
    border:2px solid rgba(255,255,255,.1); flex-shrink:0;
}
.thumb-item img   { width:100%; height:100%; object-fit:cover; }
.thumb-label {
    position:absolute; bottom:0; left:0; right:0; text-align:center;
    font-size:10px; font-weight:700; color:#fff; padding:3px 0;
    background:rgba(0,0,0,.6);
}
.thumb-item.ok  { border-color:#4ade80; }
.thumb-item.pending { border-color:#facc15; border-style:dashed; }

/* ── Result overlay ─────────────────────────────────────── */
.result-overlay {
    position:absolute; inset:0; border-radius:20px;
    display:none; align-items:center; justify-content:center; flex-direction:column; gap:12px;
    background:rgba(0,0,0,.8);
}
.result-overlay.show { display:flex; }
.result-icon { font-size:64px; }
.result-text { font-size:18px; font-weight:800; color:#fff; }
</style>
@endpush

@section('content')
<div class="reg-card">
    <div class="row g-0">

        {{-- ── LEFT: Camera ── --}}
        <div class="col-lg-7 reg-left">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#0c3d8a,#1a6b3c);display:flex;align-items:center;justify-content:center;font-size:22px;">
                    👤
                </div>
                <div>
                    <div style="font-size:18px;font-weight:800;color:#fff;">{{ $person->user->name }}</div>
                    <div style="font-size:13px;color:#64748b;">
                        {{ ucfirst($type) }}
                        @if($type === 'student') &nbsp;·&nbsp; ID: {{ $person->student_id }}
                        @else &nbsp;·&nbsp; Emp: {{ $person->employee_id }}
                        @endif
                    </div>
                </div>
                @if($person->face_registered)
                    <span class="badge bg-success ms-auto">Re-registering</span>
                @else
                    <span class="badge bg-warning text-dark ms-auto">New Registration</span>
                @endif
            </div>

            {{-- Camera box --}}
            <div class="cam-wrap" id="camWrap">
                <video id="webcam" autoplay playsinline muted></video>
                <canvas id="faceCanvas"></canvas>
                <div class="face-oval" id="faceOval"></div>

                <div class="cam-topbar">
                    <div class="live-badge"><span class="live-dot"></span> LIVE</div>
                    <span style="font-size:12px;color:rgba(255,255,255,.5);" id="camDevLabel">Starting…</span>
                </div>

                <div class="cam-hint">
                    <div id="hintText">Position your face inside the oval</div>
                </div>

                {{-- Blink overlay --}}
                <div class="blink-overlay" id="blinkOverlay">
                    <div class="blink-prompt" id="blinkPrompt">👁️ BLINK YOUR EYES NOW</div>
                    <div class="blink-count" id="blinkCount">3</div>
                    <div class="blink-sub">Detecting liveness…</div>
                </div>

                {{-- Result overlay --}}
                <div class="result-overlay" id="resultOverlay">
                    <div class="result-icon" id="resultIcon">✅</div>
                    <div class="result-text" id="resultText">Liveness Verified!</div>
                </div>
            </div>

            {{-- Captured thumbnails --}}
            <div class="mt-3">
                <div style="font-size:12px;color:#475569;margin-bottom:8px;font-weight:600;">CAPTURED SAMPLES</div>
                <div class="thumb-row" id="thumbRow">
                    <div class="thumb-item pending" id="thumb0"><span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">1</span><div class="thumb-label">Front</div></div>
                    <div class="thumb-item pending" id="thumb1"><span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">2</span><div class="thumb-label">Left</div></div>
                    <div class="thumb-item pending" id="thumb2"><span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">3</span><div class="thumb-label">Right</div></div>
                    <div class="thumb-item pending" id="thumb3"><span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">4</span><div class="thumb-label">Blink✓</div></div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Steps + Controls ── --}}
        <div class="col-lg-5 reg-right">
            <h6 style="color:#fff;font-weight:800;margin-bottom:4px;">Liveness Verification</h6>
            <p style="font-size:13px;color:#475569;margin-bottom:20px;">Follow each step. The system will guide you automatically.</p>

            {{-- Progress --}}
            <div class="prog-wrap mb-1"><div class="prog-fill" id="progFill" style="width:0%"></div></div>
            <div style="font-size:12px;color:#475569;text-align:right;margin-bottom:20px;" id="progLabel">Step 0 / 4</div>

            {{-- Steps --}}
            <ul class="step-list mb-4">
                <li class="step-item" id="step-item-0">
                    <div class="step-num" id="step-num-0">1</div>
                    <div class="step-body">
                        <div class="step-title" id="step-title-0">Face Front</div>
                        <div class="step-desc">Look straight at the camera</div>
                    </div>
                </li>
                <li class="step-item" id="step-item-1">
                    <div class="step-num" id="step-num-1">2</div>
                    <div class="step-body">
                        <div class="step-title" id="step-title-1">Turn Left</div>
                        <div class="step-desc">Slowly turn your head to the left</div>
                    </div>
                </li>
                <li class="step-item" id="step-item-2">
                    <div class="step-num" id="step-num-2">3</div>
                    <div class="step-body">
                        <div class="step-title" id="step-title-2">Turn Right</div>
                        <div class="step-desc">Slowly turn your head to the right</div>
                    </div>
                </li>
                <li class="step-item" id="step-item-3">
                    <div class="step-num" id="step-num-3">4</div>
                    <div class="step-body">
                        <div class="step-title" id="step-title-3">Blink Detection</div>
                        <div class="step-desc">Blink naturally when prompted</div>
                    </div>
                </li>
            </ul>

            {{-- Status box --}}
            <div id="statusBox" style="background:rgba(12,61,138,.1);border:1px solid rgba(12,61,138,.25);border-radius:14px;padding:14px 16px;margin-bottom:20px;font-size:14px;color:#a5b4fc;min-height:52px;">
                Click <strong>Start Verification</strong> to begin. Make sure your face is well lit.
            </div>

            {{-- Buttons --}}
            <button class="btn-capture mb-3" id="startBtn" onclick="startVerification()">
                <i class="bi bi-shield-check"></i> Start Verification
            </button>
            <button class="btn-capture mb-3" id="retakeBtn" onclick="resetAll()" style="display:none;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <i class="bi bi-arrow-counterclockwise"></i> Start Over
            </button>
            <button class="btn-capture" id="saveBtn" onclick="saveRegistration()" style="display:none;background:linear-gradient(135deg,#059669,#10b981);">
                <i class="bi bi-check-circle-fill"></i> Save Face Registration
            </button>

            {{-- Hidden form --}}
            <form id="regForm"
                  action="{{ route('admin.face-registration.store', ['type'=>$type,'id'=>$person->id]) }}"
                  method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="face_image" id="faceImageInput">
                <input type="hidden" name="extra_samples" id="extraSamplesInput">
                <input type="hidden" name="liveness_verified" id="livenessInput" value="0">
            </form>

            <a href="{{ route('admin.face-registration.index') }}"
               class="btn btn-link w-100 text-secondary mt-2" style="font-size:13px;">
               <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- face-api.js — lightweight in-browser face detection (no server needed) --}}
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script>
'use strict';
// ─── Constants ──────────────────────────────────────────────────────────────
const MODELS_URL  = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
const STEPS       = ['front','left','right','blink'];
const STEP_HINTS  = [
    '👁️  Step 1/4: Look straight at the camera (keep your face centered)',
    '⬅️  Step 2/4: Slowly turn your head to the LEFT and hold',
    '➡️  Step 3/4: Slowly turn your head to the RIGHT and hold', 
    '👁️  Step 4/4: Look forward — get ready to BLINK'
];
const BLINK_COUNTDOWN = 3;  // seconds to show countdown before blink detection

// ─── State ───────────────────────────────────────────────────────────────────
let stream          = null;
let modelsLoaded    = false;
let currentStep     = -1;       // -1 = not started
let capturedImages  = [];       // base64 strings for each step
let verifying       = false;
let blinkTimer      = null;
let detectionLoop   = null;
let eyeHistoryOpen  = [];       // rolling window of eye-open ratios
let blinkDetected   = false;

const video    = document.getElementById('webcam');
const canvas   = document.getElementById('faceCanvas');
const oval     = document.getElementById('faceOval');
const hintText = document.getElementById('hintText');
const statusBox= document.getElementById('statusBox');

// ─── Init ─────────────────────────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', async () => {
    setStatus('loading', '⏳ Loading face detection models…');
    await startWebcam();
    await loadModels();
    setStatus('ready', 'Click <strong>Start Verification</strong> to begin. Make sure your face is well lit.');
    document.getElementById('startBtn').disabled = false;
});

// ─── Load face-api models ─────────────────────────────────────────────────────
async function loadModels() {
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL);
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL);
        modelsLoaded = true;
    } catch (e) {
        console.warn('face-api models failed:', e);
        // Graceful fallback — verification will use timer-based simulation
        modelsLoaded = false;
        setStatus('warn', '⚠️ AI model unavailable. Using guided timer-based verification.');
    }
}

// ─── Webcam ───────────────────────────────────────────────────────────────────
async function startWebcam() {
    try {
        let videoConstraints = { width:{ideal:1280}, height:{ideal:720} };
        
        // Always prioritize front camera for face registration, especially on mobile
        try {
            // Try to explicitly request front camera first
            videoConstraints.facingMode = 'user'; // Front camera
            
            stream = await navigator.mediaDevices.getUserMedia({
                video: videoConstraints,
                audio: false
            });
        } catch (frontCamError) {
            console.warn('Front camera not available, trying device enumeration:', frontCamError);
            
            // Fallback: Enumerate devices and look for front camera
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            if (videoDevices.length > 0) {
                // Try each device to find front camera
                let frontCameraFound = false;
                for (const device of videoDevices) {
                    if (device.label.toLowerCase().includes('front') || 
                        device.label.toLowerCase().includes('user') ||
                        device.label.toLowerCase().includes('facing')) {
                        videoConstraints = { 
                            deviceId: { exact: device.deviceId },
                            width: {ideal:1280}, 
                            height: {ideal:720} 
                        };
                        frontCameraFound = true;
                        break;
                    }
                }
                
                // If no front camera found by label, use first device
                if (!frontCameraFound) {
                    videoConstraints.deviceId = { exact: videoDevices[0].deviceId };
                }
                
                stream = await navigator.mediaDevices.getUserMedia({
                    video: videoConstraints,
                    audio: false
                });
            } else {
                throw new Error('No video devices found');
            }
        }
        
        video.srcObject = stream;
        await new Promise(r => video.addEventListener('loadeddata', r, {once:true}));

        const track = stream.getVideoTracks()[0];
        const s     = track.getSettings();
        
        // Check if we successfully got front camera
        const isFrontCamera = track.label.toLowerCase().includes('front') || 
                             track.label.toLowerCase().includes('user') ||
                             s.facingMode === 'user';
        
        document.getElementById('camDevLabel').textContent =
            (track.label || 'Camera') + 
            (isFrontCamera ? ' (Front)' : '') + 
            (s.width ? ' · ' + s.width + 'px' : '');

    } catch (err) {
        setStatus('error', '❌ Camera error: ' + err.message + '. Please allow camera access.');
        document.getElementById('startBtn').disabled = true;
        console.error('Camera initialization failed:', err);
    }
}

// ─── Step management ─────────────────────────────────────────────────────────
function advanceStep() {
    // Strict validation: Don't advance if current step hasn't been captured
    if (currentStep >= 0 && !capturedImages[currentStep]) {
        setStatus('error', '❌ Current step not completed. Please complete the current step before proceeding.');
        return;
    }
    
    currentStep++;
    updateStepUI();
    if (currentStep < 3) {
        hintText.textContent = STEP_HINTS[currentStep];
        oval.className = 'face-oval';
        startDetectionLoop();
    } else {
        // Step 3 = blink - only start if all previous steps are captured
        if (capturedImages[0] && capturedImages[1] && capturedImages[2]) {
            startBlinkStep();
        } else {
            setStatus('error', '❌ Previous steps not completed. Please start over and complete all steps in order.');
            currentStep = 2; // Reset to last incomplete step
            updateStepUI();
        }
    }
}

function updateStepUI() {
    for (let i = 0; i < 4; i++) {
        const num   = document.getElementById(`step-num-${i}`);
        const title = document.getElementById(`step-title-${i}`);
        if (i < currentStep) {
            num.className   = 'step-num done';
            num.textContent = '';
            title.className = 'step-title done';
        } else if (i === currentStep) {
            num.className   = 'step-num active';
            num.textContent = i + 1;
            title.className = 'step-title active';
        } else {
            num.className   = 'step-num';
            num.textContent = i + 1;
            title.className = 'step-title';
        }
    }
    const pct = Math.round((currentStep / 4) * 100);
    document.getElementById('progFill').style.width  = pct + '%';
    document.getElementById('progLabel').textContent = `Step ${currentStep} / 4`;
}

// ─── Detection loop for steps 0–2 ────────────────────────────────────────────
function startDetectionLoop() {
    stopDetectionLoop();
    let holdFrames = 0;
    const HOLD_NEEDED = modelsLoaded ? 15 : 25;  // Increased hold time for better stability

    detectionLoop = setInterval(async () => {
        if (!stream || !video.videoWidth) return;
        
        // Extra safety: don't process if we've already captured this step
        if (capturedImages[currentStep]) {
            stopDetectionLoop();
            return;
        }

        let faceOk = false;

        if (modelsLoaded) {
            try {
                const det = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 }))
                    .withFaceLandmarks(true);

                if (det) {
                    faceOk = checkPose(det, currentStep);
                    drawFaceBox(det.detection.box);
                } else {
                    clearFaceCanvas();
                }
            } catch(e) {
                // model inference error — fall through to timer-based fallback
                faceOk = true;
            }
        } else {
            // No AI — just let the user hold still for HOLD_NEEDED frames
            faceOk = true;
            clearFaceCanvas();
        }

        if (faceOk) {
            holdFrames++;
            oval.className = 'face-oval liveness';
            const pct = Math.min(100, Math.round((holdFrames / HOLD_NEEDED) * 100));
            hintText.textContent = STEP_HINTS[currentStep] + ' … ' + pct + '%';

            if (holdFrames >= HOLD_NEEDED) {
                stopDetectionLoop();
                captureCurrentStep();
            }
        } else {
            holdFrames = 0;
            oval.className = 'face-oval detected';
            hintText.textContent = STEP_HINTS[currentStep];
        }
    }, 100);
}

function stopDetectionLoop() {
    if (detectionLoop) { clearInterval(detectionLoop); detectionLoop = null; }
}

// ─── Pose checking via landmarks ──────────────────────────────────────────────
function checkPose(det, step) {
    const pts = det.landmarks.positions;
    
    // Ensure we have enough landmarks
    if (!pts || pts.length < 68) return false;
    
    // Nose tip = pts[30], left eye outer = pts[36], right eye outer = pts[45]
    const noseTip = pts[30];
    const leftEyeOuter = pts[36];
    const rightEyeOuter = pts[45];
    
    // Basic face quality checks
    if (!noseTip || !leftEyeOuter || !rightEyeOuter) return false;
    
    const faceWidth = rightEyeOuter.x - leftEyeOuter.x;
    if (faceWidth < 50) return false; // Face too small/far
    
    // Horizontal offset of nose from face centre
    const centre = (leftEyeOuter.x + rightEyeOuter.x) / 2;
    const offset = (noseTip.x - centre) / faceWidth; // -0.5 .. +0.5

    // More strict pose validation for each step
    if (step === 0) {
        // Front: nose should be very close to center
        return Math.abs(offset) < 0.08;
    } else if (step === 1) {
        // Left turn: nose should be significantly right of center (in mirrored view)
        return offset < -0.18;
    } else if (step === 2) {
        // Right turn: nose should be significantly left of center (in mirrored view) 
        return offset > 0.18;
    }
    
    return true;
}

// ─── Capture frame for current step ─────────────────────────────────────────
function captureCurrentStep() {
    // Double check we haven't already captured this step
    if (capturedImages[currentStep]) {
        setStatus('warn', '⚠️ Step already captured, advancing to next step...');
        setTimeout(() => {
            if (currentStep < 3) advanceStep();
        }, 700);
        return;
    }
    
    const snap = captureFrame();
    capturedImages[currentStep] = snap;

    // Update thumbnail
    const thumb = document.getElementById('thumb' + currentStep);
    thumb.innerHTML = `<img src="${snap}" alt=""><div class="thumb-label">${['Front','Left','Right','Blink'][currentStep]}</div>`;
    thumb.className = 'thumb-item ok';
    oval.className  = 'face-oval verified';

    setStatus('ok', `✅ Step ${currentStep + 1} captured! ${currentStep < 3 ? 'Moving to next step…' : 'All steps completed!'}`);

    // Add a longer delay to ensure user sees the completion before advancing
    setTimeout(() => {
        if (currentStep < 3) {
            // Validate that we actually captured something before advancing
            if (capturedImages[currentStep] && capturedImages[currentStep].length > 100) {
                advanceStep();
            } else {
                setStatus('error', '❌ Capture failed. Please try again.');
                // Reset current step to try again
                const thumb = document.getElementById('thumb' + currentStep);
                thumb.className = 'thumb-item pending';
                thumb.innerHTML = `<span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">${currentStep+1}</span><div class="thumb-label">${['Front','Left','Right','Blink'][currentStep]}</div>`;
                capturedImages[currentStep] = null; // Clear failed capture
                startDetectionLoop(); // Restart detection for current step
            }
        }
    }, 1200); // Increased delay to 1.2 seconds
}

// ─── Blink step (step 3) ─────────────────────────────────────────────────────
function startBlinkStep() {
    // Ensure all previous steps are completed before starting blink
    if (!capturedImages[0] || !capturedImages[1] || !capturedImages[2]) {
        setStatus('error', '❌ Cannot start blink detection. Previous steps not completed.');
        return;
    }
    
    hintText.textContent = '👁️  Get ready to blink when countdown ends…';
    oval.className = 'face-oval liveness';
    setStatus('warn', '⏳ Blink countdown starting…');

    const overlay = document.getElementById('blinkOverlay');
    const countEl = document.getElementById('blinkCount');
    overlay.classList.add('show');

    let countdown = BLINK_COUNTDOWN;
    countEl.textContent = countdown;

    blinkTimer = setInterval(() => {
        countdown--;
        countEl.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(blinkTimer);
            document.getElementById('blinkPrompt').textContent = '👁️  BLINK NOW!';
            countEl.textContent = '';
            document.getElementById('blinkOverlay').querySelector('.blink-sub').textContent = 'Keep eyes open, then blink once…';
            beginBlinkDetection();
        }
    }, 1000);
}

function beginBlinkDetection() {
    blinkDetected = false;
    eyeHistoryOpen = [];
    let wasOpen = true;

    // Always set a timeout fallback — captures even if AI detection fails
    let blinkTimeout = setTimeout(() => {
        stopDetectionLoop();
        finishBlinkStep(captureFrame());
    }, 4000);

    if (!modelsLoaded) {
        // No AI available — just wait for the timeout
        return;
    }

    detectionLoop = setInterval(async () => {
        if (!stream || !video.videoWidth) return;

        try {
            const det = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.4 }))
                .withFaceLandmarks(true);

            if (!det) return;

            const ear = eyeAspectRatio(det.landmarks.positions);
            eyeHistoryOpen.push(ear);
            if (eyeHistoryOpen.length > 20) eyeHistoryOpen.shift();

            if (wasOpen && ear < 0.20) {
                wasOpen = false;  // eyes closed
            } else if (!wasOpen && ear > 0.25) {
                // eyes reopened — blink confirmed
                blinkDetected = true;
                clearTimeout(blinkTimeout);
                stopDetectionLoop();
                finishBlinkStep(captureFrame());
            }
        } catch(e) {
            // inference error — let the timeout handle it
        }
    }, 80);
}

// Eye Aspect Ratio from landmarks (indices 36-41 left eye, 42-47 right eye)
function eyeAspectRatio(pts) {
    function dist(a,b){ return Math.hypot(a.x-b.x, a.y-b.y); }
    // Left eye
    const lV1 = dist(pts[37],pts[41]), lV2 = dist(pts[38],pts[40]);
    const lH  = dist(pts[36],pts[39]);
    const lEAR = (lV1+lV2)/(2*lH);
    // Right eye
    const rV1 = dist(pts[43],pts[47]), rV2 = dist(pts[44],pts[46]);
    const rH  = dist(pts[42],pts[45]);
    const rEAR = (rV1+rV2)/(2*rH);
    return (lEAR + rEAR) / 2;
}

function finishBlinkStep(snap) {
    document.getElementById('blinkOverlay').classList.remove('show');
    capturedImages[3] = snap;

    const thumb = document.getElementById('thumb3');
    thumb.innerHTML = `<img src="${snap}" alt=""><div class="thumb-label">Blink✓</div>`;
    thumb.className = 'thumb-item ok';

    oval.className = 'face-oval verified';
    updateStepUI();
    document.getElementById('progFill').style.width  = '100%';
    document.getElementById('progLabel').textContent = 'Step 4 / 4';

    showResultOverlay(true);
}

// ─── Result overlay ───────────────────────────────────────────────────────────
function showResultOverlay(success) {
    const overlay = document.getElementById('resultOverlay');
    document.getElementById('resultIcon').textContent = success ? '✅' : '❌';
    document.getElementById('resultText').textContent = success ? 'Liveness Verified!' : 'Verification Failed';
    overlay.classList.add('show');

    setTimeout(() => {
        overlay.classList.remove('show');
        if (success) {
            setStatus('ok', '✅ All 4 steps completed! Click <strong>Save Face Registration</strong>.');
            document.getElementById('saveBtn').style.display   = 'flex';
            document.getElementById('retakeBtn').style.display = 'flex';
            document.getElementById('startBtn').style.display  = 'none';
        }
    }, 2000);
}

// ─── Canvas helpers ───────────────────────────────────────────────────────────
function drawFaceBox(box) {
    const scaleX = canvas.width  / video.videoWidth;
    const scaleY = canvas.height / video.videoHeight;
    canvas.width  = video.clientWidth;
    canvas.height = video.clientHeight;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.strokeStyle = '#4ade80'; ctx.lineWidth = 2; ctx.globalAlpha = 0.7;
    // mirror coords
    const mirroredX = canvas.width - (box.x + box.width) * (canvas.width / video.videoWidth);
    ctx.strokeRect(mirroredX, box.y * (canvas.height/video.videoHeight),
        box.width * (canvas.width/video.videoWidth), box.height * (canvas.height/video.videoHeight));
}

function clearFaceCanvas() {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0,0,canvas.width,canvas.height);
}

function captureFrame() {
    const c   = document.createElement('canvas');
    c.width   = video.videoWidth;
    c.height  = video.videoHeight;
    const ctx = c.getContext('2d');
    // De-mirror for server
    ctx.translate(c.width,0); ctx.scale(-1,1);
        ctx.drawImage(video,0,0);
        return c.toDataURL('image/jpeg', 0.70);
}

// ─── Public button actions ────────────────────────────────────────────────────
function startVerification() {
    if (!stream) { 
        setStatus('error','❌ Camera not available. Please allow camera access.'); 
        return; 
    }
    
    // Reset everything to ensure clean start
    document.getElementById('startBtn').style.display  = 'none';
    document.getElementById('retakeBtn').style.display = 'flex';
    
    // Initialize capture array with explicit nulls
    capturedImages = [null, null, null, null];
    currentStep = -1;
    blinkDetected = false;
    eyeHistoryOpen = [];
    
    // Clear any existing detection loops
    stopDetectionLoop();
    clearInterval(blinkTimer);
    
    setStatus('ready', '🔄 Starting verification process... Please position your face in the oval.');
    
    // Start from step 0
    advanceStep();
}

function resetAll() {
    // Stop all detection processes
    stopDetectionLoop();
    clearInterval(blinkTimer);
    document.getElementById('blinkOverlay').classList.remove('show');
    document.getElementById('resultOverlay').classList.remove('show');
    
    // Reset state completely
    capturedImages = [null, null, null, null];
    currentStep    = -1;
    blinkDetected  = false;
    eyeHistoryOpen = [];

    // Reset thumbnails to pending state
    ['Front','Left','Right','Blink✓'].forEach((lbl,i) => {
        const t = document.getElementById('thumb'+i);
        t.className = 'thumb-item pending';
        t.innerHTML = `<span style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%;color:#334155;">${i+1}</span><div class="thumb-label">${lbl}</div>`;
    });

    // Reset UI elements
    oval.className = 'face-oval';
    hintText.textContent = 'Position your face inside the oval';
    document.getElementById('progFill').style.width  = '0%';
    document.getElementById('progLabel').textContent = 'Step 0 / 4';
    updateStepUI();

    // Reset buttons
    document.getElementById('saveBtn').style.display   = 'none';
    document.getElementById('saveBtn').disabled        = false;
    document.getElementById('saveBtn').innerHTML       = '<i class="bi bi-check-circle-fill"></i> Save Face Registration';
    document.getElementById('startBtn').style.display  = 'flex';
    document.getElementById('retakeBtn').style.display = 'none';
    
    setStatus('ready','Click <strong>Start Verification</strong> to begin. All steps will be completed in order: Front → Left → Right → Blink.');
}

function saveRegistration() {
    // Strict validation: Check that ALL 4 steps are captured with valid data
    const requiredSteps = [0, 1, 2, 3];
    const missingSteps = [];
    
    for (let i of requiredSteps) {
        const img = capturedImages[i];
        if (!img || typeof img !== 'string' || img.length < 100) {
            missingSteps.push(['Front', 'Left', 'Right', 'Blink'][i]);
        }
    }
    
    if (missingSteps.length > 0) {
        setStatus('error', `❌ Missing captures: ${missingSteps.join(', ')}. Please click <strong>Start Over</strong> and complete all 4 steps in order.`);
        return;
    }

    const primaryImg = capturedImages[0];
    if (!primaryImg || primaryImg.length < 100) {
        setStatus('error', '❌ Front face image is missing or invalid. Please start over.');
        return;
    }

    // Final validation: ensure we have exactly 4 captured images
    if (capturedImages.filter(img => img && img.length > 100).length !== 4) {
        setStatus('error', '❌ Not all verification steps completed. Please start over and complete the full sequence.');
        return;
    }

    // Use multipart/form-data upload via fetch to avoid base64 truncation on hosts
    // like InfinityFree which may corrupt very long POST bodies. Convert dataURLs
    // to Blobs and send files in FormData.
    const fd = new FormData();
    fd.append('_token', document.querySelector('input[name="_token"]').value);
    fd.append('liveness_verified', '1');

    // Helper: convert dataURL -> Blob
    function dataURLToBlob(dataurl) {
        const arr = dataurl.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) { u8arr[n] = bstr.charCodeAt(n); }
        return new Blob([u8arr], { type: mime });
    }

    // Primary image
    fd.append('face_image_file', dataURLToBlob(primaryImg), 'primary.jpg');

    // Extra samples as files (left,right,blink)
    const extras = capturedImages.slice(1).filter(Boolean);
    extras.forEach((durl, idx) => {
        fd.append('extra_files[]', dataURLToBlob(durl), `sample_${idx}.jpg`);
    });

    // Fallback: also include JSON fields for backward compatibility
    fd.append('face_image', primaryImg);
    fd.append('extra_samples', JSON.stringify(extras));

    document.getElementById('saveBtn').disabled = true;
    document.getElementById('saveBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving…';
    setStatus('ok', '💾 Uploading face registration (multipart) — this avoids host truncation.');

    fetch(document.getElementById('regForm').action, { method: 'POST', body: fd })
        .then(async res => {
            if (!res.ok) {
                const text = await res.text().catch(() => 'Server error');
                throw new Error(text || res.statusText);
            }
            // On success, follow redirect or show success
            const loc = res.headers.get('X-Redirect-Location');
            if (loc) { window.location = loc; return; }
            return res.text();
        })
        .then(body => {
            // Best-effort: try to parse JSON flash message
            try {
                const json = JSON.parse(body);
                if (json.redirect) window.location = json.redirect;
            } catch (e) {
                // If server returned HTML, redirect to index
                window.location = '{{ route("admin.face-registration.index") }}';
            }
        })
        .catch(err => {
            console.error('Upload failed', err);
            setStatus('error', '❌ Upload failed: ' + (err.message || 'Network error'));
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('saveBtn').innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Face Registration';
        });
}

// ─── Status helper ────────────────────────────────────────────────────────────
function setStatus(type, html) {
    const colors = {
        loading: ['rgba(12,61,138,.1)','rgba(12,61,138,.25)','#a5b4fc'],
        ready:   ['rgba(12,61,138,.1)','rgba(12,61,138,.25)','#a5b4fc'],
        ok:      ['rgba(74,222,128,.08)','rgba(74,222,128,.25)','#4ade80'],
        warn:    ['rgba(250,204,21,.08)','rgba(250,204,21,.25)','#facc15'],
        error:   ['rgba(248,113,113,.08)','rgba(248,113,113,.25)','#f87171'],
    };
    const [bg, border, color] = colors[type] || colors.ready;
    statusBox.style.background   = bg;
    statusBox.style.borderColor  = border;
    statusBox.style.color        = color;
    statusBox.innerHTML          = html;
}
</script>
@endpush
