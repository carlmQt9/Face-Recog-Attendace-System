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
/* ── Roster badges ─────────────────────────────────── */
.badge-face  {background:rgba(79,70,229,.2);color:#818cf8;}
.badge-manual{background:rgba(250,204,21,.15);color:#facc15;}
.badge-qr    {background:rgba(6,182,212,.15);color:#22d3ee;}
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

/* ── Scan type toggle (Time-In / Time-Out) ─────────── */
.scantype-toggle{
    display:flex;gap:4px;background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:4px;
}
.scantype-btn{
    font-size:12px;font-weight:700;border:none;border-radius:9px;
    padding:6px 16px;cursor:pointer;transition:all .2s;color:rgba(255,255,255,.5);background:transparent;
    display:flex;align-items:center;gap:5px;
}
.scantype-btn.in.active  {background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;}
.scantype-btn.out.active {background:linear-gradient(135deg,#dc2626,#f87171);color:#fff;}

.scan-btn{
    background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;
    border:none;border-radius:12px;padding:9px 22px;font-size:13px;font-weight:700;
    cursor:pointer;transition:opacity .15s,transform .15s;
    display:flex;align-items:center;gap:7px;
}
.scan-btn:hover{opacity:.85;transform:translateY(-1px);}
.scan-btn:disabled{opacity:.35;cursor:not-allowed;}

/* ── Camera switch / icon buttons ──────────────────── */
.cam-switch-btn{
    background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.12);
    color:#fff;border-radius:10px;padding:7px 13px;font-size:13px;
    cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;
}
.cam-switch-btn:hover{background:rgba(255,255,255,.16);}

/* Compact icon-only button */
.cam-icon-btn{
    background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.12);
    color:#fff;border-radius:9px;padding:6px 10px;font-size:14px;
    cursor:pointer;transition:background .2s, transform .1s;
    display:inline-flex;align-items:center;justify-content:center;
    text-decoration:none;line-height:1;
}
.cam-icon-btn:hover { background:rgba(255,255,255,.18); color:#fff; }
.cam-icon-btn.danger { background:rgba(220,38,38,.2); border-color:rgba(220,38,38,.4); color:#f87171; }
.cam-icon-btn.danger:hover { background:rgba(220,38,38,.35); }

/* Small PST clock pill */
.cam-pill {
    background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
    border-radius:9px;padding:5px 10px;font-size:13px;font-weight:700;
    display:inline-flex;align-items:center;
}

/* ── Confidence bar ────────────────────────────────── */
.conf-bar-wrap{height:4px;background:rgba(255,255,255,.08);border-radius:50px;overflow:hidden;width:80px;}
.conf-bar{height:100%;border-radius:50px;background:#4ade80;transition:width .3s;}

/* ── Signal pills (compact) ────────────────────────── */
.signal-pill{
    display:inline-flex;align-items:center;gap:5px;
    font-size:11px;font-weight:700;border-radius:50px;
    padding:4px 11px;white-space:nowrap;
}
.signal-ok  {background:rgba(74,222,128,.1); color:#4ade80; border:1px solid rgba(74,222,128,.25);}
.signal-err {background:rgba(248,113,113,.1);color:#f87171; border:1px solid rgba(248,113,113,.25);}
.signal-wait{background:rgba(250,204,21,.1); color:#facc15; border:1px solid rgba(250,204,21,.25);}

/* ── QR scan frame (shown in QR mode) ─────────────── */
.qr-frame {
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-55%);
    width:54%; aspect-ratio:1;
    border:3px solid #06b6d4;
    border-radius:16px;
    box-shadow:0 0 0 3000px rgba(0,0,0,.55), 0 0 32px rgba(6,182,212,.8);
    display:none; pointer-events:none;
    animation:qrPulse 1.4s ease-in-out infinite;
    transition:border-color .2s, box-shadow .2s;
}
.qr-frame.active   { display:block; }
.qr-frame.detected {
    border-color:#4ade80;
    box-shadow:0 0 0 3000px rgba(0,0,0,.55), 0 0 48px rgba(74,222,128,1);
    animation:none;
}
@keyframes qrPulse{
    0%,100%{border-color:#06b6d4;box-shadow:0 0 0 3000px rgba(0,0,0,.55),0 0 24px rgba(6,182,212,.6)}
    50%{border-color:#818cf8;box-shadow:0 0 0 3000px rgba(0,0,0,.55),0 0 40px rgba(129,140,248,.9)}
}

/* Corner accent marks on QR frame */
.qr-frame .qc { position:absolute; width:20px; height:20px; border-color:inherit; border-style:solid; }
.qr-frame .qc.tl { top:-1px;    left:-1px;    border-width:4px 0 0 4px; border-radius:4px 0 0 0; }
.qr-frame .qc.tr { top:-1px;    right:-1px;   border-width:4px 4px 0 0; border-radius:0 4px 0 0; }
.qr-frame .qc.bl { bottom:-1px; left:-1px;    border-width:0 0 4px 4px; border-radius:0 0 0 4px; }
.qr-frame .qc.br { bottom:-1px; right:-1px;   border-width:0 4px 4px 0; border-radius:0 0 4px 0; }

/* QR hint label inside frame */
.qr-hint {
    position:absolute; bottom:-36px; left:50%; transform:translateX(-50%);
    background:rgba(0,0,0,.75); color:#fff; font-size:12px; font-weight:700;
    padding:4px 14px; border-radius:50px; white-space:nowrap;
    border:1px solid rgba(255,255,255,.15);
}

/* ── QR modal ──────────────────────────────────────── */
.qr-modal-backdrop{
    position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1050;
    display:flex;align-items:center;justify-content:center;
    opacity:0;pointer-events:none;transition:opacity .25s;
}
.qr-modal-backdrop.open{opacity:1;pointer-events:all;}
.qr-modal{
    background:#0f172a;border:1px solid rgba(255,255,255,.1);
    border-radius:24px;padding:32px;text-align:center;
    max-width:380px;width:90%;
    animation:popIn .3s cubic-bezier(.34,1.56,.64,1) both;
}
.qr-modal h6{color:#fff;font-weight:800;font-size:17px;margin-bottom:4px;}
.qr-modal .qr-sub{color:#64748b;font-size:13px;margin-bottom:20px;}
#qrCanvas{border-radius:16px;background:#fff;padding:12px;display:block;margin:0 auto 16px;}
.qr-link{
    display:flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
    border-radius:10px;padding:9px 12px;margin-bottom:16px;
}
.qr-link input{
    flex:1;background:transparent;border:none;outline:none;
    color:#94a3b8;font-size:12px;font-family:monospace;
}
.qr-link button{
    background:rgba(79,70,229,.2);border:1px solid rgba(79,70,229,.4);
    color:#818cf8;border-radius:7px;padding:4px 10px;font-size:12px;
    cursor:pointer;white-space:nowrap;
}
.qr-link button:hover{background:rgba(79,70,229,.35);}

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
}</style>
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
                    &nbsp;
                    @if($session->session_type === 'afternoon_out')
                        <span class="badge" style="background:#7f1d1d;color:#fca5a5;">🌇 Afternoon — Time Out</span>
                    @else
                        <span class="badge" style="background:#14532d;color:#86efac;">🌅 Morning — Time In</span>
                    @endif
                    @if($session->scheduled_start && $session->scheduled_end)
                        &nbsp;<span class="badge" style="background:#1e3a5f;color:#93c5fd;">
                            ⏰ {{ \Carbon\Carbon::parse($session->scheduled_start)->format('h:i A') }}
                            – {{ \Carbon\Carbon::parse($session->scheduled_end)->format('h:i A') }}
                        </span>
                        &nbsp;<span id="autoEndCountdown" style="font-size:11px;color:#facc15;font-weight:700;"></span>
                    @endif
                </small>
            </div>
            <div class="d-flex gap-1 align-items-center flex-wrap">
                {{-- Compact PST clock --}}
                <span class="cam-pill" style="font-family:monospace;color:#4ade80;min-width:76px;text-align:center;" id="camPhTime">--:-- --</span>

                {{-- Divider --}}
                <span style="color:rgba(255,255,255,.15);font-size:18px;">|</span>

                @if($session->camera->is_local_device)
                <button class="cam-icon-btn" onclick="switchCamera()" title="Switch front/rear camera">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
                @endif

                {{-- Scan mode: Auto / Manual / QR --}}
                <div class="mode-toggle">
                    <button class="mode-btn active" id="modeAuto" onclick="setMode('auto')" title="Auto face scan">
                        <i class="bi bi-magic"></i><span class="d-none d-md-inline ms-1">Auto</span>
                    </button>
                    <button class="mode-btn" id="modeManual" onclick="setMode('manual')" title="Manual scan">
                        <i class="bi bi-hand-index"></i><span class="d-none d-md-inline ms-1">Manual</span>
                    </button>
                    <button class="mode-btn" id="modeQr" onclick="setMode('qr')" title="QR code scan">
                        <i class="bi bi-qr-code-scan"></i><span class="d-none d-md-inline ms-1">QR</span>
                    </button>
                </div>

                {{-- Divider --}}
                <span style="color:rgba(255,255,255,.15);font-size:18px;">|</span>

                {{-- Scan type: In / Out --}}
                @if($session->isActive())
                <div class="scantype-toggle">
                    <button class="scantype-btn in active" id="scanTypeIn" onclick="setScanType('time_in')" title="Time In">
                        <i class="bi bi-box-arrow-in-right"></i> In
                    </button>
                    <button class="scantype-btn out" id="scanTypeOut" onclick="setScanType('time_out')" title="Time Out">
                        <i class="bi bi-box-arrow-right"></i> Out
                    </button>
                </div>
                @endif

                {{-- Divider --}}
                <span style="color:rgba(255,255,255,.15);font-size:18px;">|</span>

                {{-- QR modal, End, Home --}}
                @if($session->isActive())
                <button class="cam-icon-btn" onclick="openQr()" title="Show session QR code">
                    <i class="bi bi-qr-code"></i>
                </button>
                <form action="{{ route('teacher.sessions.stop', $session) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="cam-icon-btn danger" title="End session"
                            onclick="handleEndSession(this)">
                        <i class="bi bi-stop-circle-fill"></i>
                    </button>
                </form>
                @endif
                <a href="{{ route('teacher.sessions.index') }}" class="cam-icon-btn"
                   style="text-decoration:none;" title="Back to My Sessions (session stays active)">
                    <i class="bi bi-house-fill"></i>
                </a>
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

            {{-- QR mode frame (shown when mode=qr) --}}
            <div class="qr-frame" id="qrFrame">
                <div class="qc tl"></div>
                <div class="qc tr"></div>
                <div class="qc bl"></div>
                <div class="qc br"></div>
                <div class="qr-hint" id="qrHint">Hold QR card here</div>
            </div>

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

        {{-- Signal guide — compact pill row --}}
        <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
            <span style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Signals:</span>
            <span class="signal-pill signal-ok">🔊 1 Beep — Match</span>
            <span class="signal-pill signal-err">🔊🔊 2 Beeps — No Match</span>
            <span class="signal-pill signal-wait" id="nextInLabel">⏳ Ready</span>
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
                    @if($record->student->face_encoding && Storage::disk('public')->exists($record->student->face_encoding))
                        <img src="{{ Storage::url($record->student->face_encoding) }}"
                             alt="{{ $record->student->user->name }}"
                             style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,.15);">
                    @else
                        <div class="roster-avatar">👤</div>
                    @endif
                    <div>
                        <div class="roster-name">{{ $record->student->user->name }}</div>
                        <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                            @if($record->scan_type === 'time_out')
                                <span style="font-size:10px;background:rgba(220,38,38,.15);color:#f87171;border-radius:5px;padding:2px 7px;font-weight:700;">
                                    OUT {{ $record->time_out?->format('h:i A') }}
                                </span>
                            @else
                                <span style="font-size:10px;background:rgba(22,163,74,.15);color:#4ade80;border-radius:5px;padding:2px 7px;font-weight:700;">
                                    IN {{ $record->arrived_at->format('h:i A') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="roster-badge
                        {{ $record->method==='manual' ? 'badge-manual' : ($record->method==='qr_code' ? 'badge-qr' : 'badge-face') }}">
                        {{ $record->method==='manual' ? 'Manual' : ($record->method==='qr_code' ? 'QR' : 'Face') }}
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

{{-- ── QR Attendance Modal ── --}}
@if($session->isActive())
<div class="qr-modal-backdrop" id="qrBackdrop" onclick="closeQrOutside(event)">
    <div class="qr-modal">
        <h6><i class="bi bi-qr-code-scan me-2 text-primary"></i>QR Code Attendance</h6>
        <p class="qr-sub">Students scan this with their phone camera to mark themselves present</p>
        <canvas id="qrCanvas" width="200" height="200"></canvas>
        <div class="qr-link">
            <input type="text" id="qrLinkInput" readonly>
            <button onclick="copyQrLink()"><i class="bi bi-clipboard me-1"></i>Copy</button>
        </div>
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-sm btn-outline-secondary" onclick="closeQr()"
                    style="border-radius:9px;color:#94a3b8;border-color:#334155;">
                <i class="bi bi-x-lg me-1"></i>Close
            </button>
            <button class="btn btn-sm btn-primary" onclick="downloadQr()"
                    style="border-radius:9px;">
                <i class="bi bi-download me-1"></i>Download
            </button>
        </div>
        <p style="font-size:11px;color:#475569;margin-top:14px;margin-bottom:0;">
            QR expires when the session ends &nbsp;·&nbsp; each student can only mark once
        </p>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
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
const DEFAULT_SCAN_TYPE = '{{ $session->defaultScanType() }}';  // from session_type

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
let scanMode            = 'auto'; // 'auto' | 'manual' | 'qr'
let scanType            = DEFAULT_SCAN_TYPE;  // auto-set from session type
let inCooldown          = false;
let rosterCount         = {{ $attendance->count() }};
// Track which students are already marked in this session (client-side dedup)
// Key format: "studentId:scan_type" e.g. "5:time_in" or "5:time_out"
let markedIds = new Set([
    @foreach($attendance as $r)
        '{{ $r->student_id }}:{{ $r->scan_type ?? "time_in" }}',
    @endforeach
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
    // Apply session type to UI immediately
    setScanType(DEFAULT_SCAN_TYPE);

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
        const resp = await fetch('/api/face-descriptors?session_id=' + SESSION_ID, { headers: { Accept: 'application/json' } });
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
//  SCAN TYPE — Time In / Time Out
// ═══════════════════════════════════════════════════════
function setScanType(type) {
    scanType = type;
    document.getElementById('scanTypeIn').classList.toggle('active',  type === 'time_in');
    document.getElementById('scanTypeOut').classList.toggle('active', type === 'time_out');

    const isOut = type === 'time_out';
    // Update camera border tint to hint the mode
    wrapper.style.setProperty('--scan-tint', isOut ? 'rgba(220,38,38,.3)' : 'rgba(22,163,74,.3)');
    setStatus(
        isOut ? '🚪 Time-Out mode — scan to log student departure' : '🏫 Time-In mode — scan to log student arrival',
        isOut ? 'wait' : 'info'
    );
}
function setMode(mode) {
    scanMode = mode;
    document.getElementById('modeAuto').classList.toggle('active', mode === 'auto');
    document.getElementById('modeManual').classList.toggle('active', mode === 'manual');
    document.getElementById('modeQr').classList.toggle('active', mode === 'qr');

    // Toggle QR frame overlay
    const qrFrame = document.getElementById('qrFrame');
    if (qrFrame) qrFrame.classList.toggle('active', mode === 'qr');

    // Stop whatever is running
    stopAutoScan();
    stopQrScan();

    // Clear face detection canvas when switching modes
    drawNoFace();
    updateConfidence(0);

    if (mode === 'auto') {
        if (scanBtn) scanBtn.style.display = 'none';
        if (!inCooldown && IS_ACTIVE) startAutoScan();
        setStatus('🔍 Auto-scanning — stand in front of the camera', 'info');
    } else if (mode === 'qr') {
        if (scanBtn) scanBtn.style.display = 'none';
        if (!inCooldown && IS_ACTIVE) startQrScan();
        setStatus('📷 QR mode — hold student QR card up to the camera', 'info');
    } else {
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

// ═══════════════════════════════════════════════════════
//  QR SCAN LOOP — jsQR decodes student QR from live feed
// ═══════════════════════════════════════════════════════
let qrScanInterval  = null;
let lastQrToken     = null;   // debounce same token

function startQrScan() {
    stopQrScan();
    qrScanInterval = setInterval(runQrScan, 120);   // ~8fps — faster detection
}

function stopQrScan() {
    if (qrScanInterval) { clearInterval(qrScanInterval); qrScanInterval = null; }
}

async function runQrScan() {
    if (inCooldown || !stream || !video.videoWidth) return;

    // Always clear face detection canvas in QR mode
    drawNoFace();
    updateConfidence(0);

    const vw = video.videoWidth;
    const vh = video.videoHeight;

    // ── Strategy 1: Full frame at native res ─────────────────
    const fullCanvas = document.createElement('canvas');
    fullCanvas.width  = vw;
    fullCanvas.height = vh;
    const fullCtx = fullCanvas.getContext('2d', { willReadFrequently: true });

    // Mirror-compensate: if video is CSS-mirrored, flip the canvas draw so
    // the QR data orientation is correct for jsQR
    if (video.style.transform === 'scaleX(-1)') {
        fullCtx.translate(vw, 0);
        fullCtx.scale(-1, 1);
    }
    fullCtx.drawImage(video, 0, 0);

    // ── Contrast boost — helps low-light QR codes ────────────
    fullCtx.filter = 'contrast(1.6) brightness(1.15)';
    fullCtx.drawImage(fullCanvas, 0, 0);
    fullCtx.filter = 'none';

    let code = null;

    // Try 1: full frame, normal
    const fullData = fullCtx.getImageData(0, 0, vw, vh);
    code = jsQR(fullData.data, fullData.width, fullData.height, {
        inversionAttempts: 'attemptBoth'   // handles dark-on-light AND light-on-dark
    });

    // Try 2: center crop (640×640 max) — faster + focuses on QR frame overlay area
    if (!code) {
        const cropSize = Math.min(vw, vh, 640);
        const cx = Math.floor((vw - cropSize) / 2);
        const cy = Math.floor((vh - cropSize) / 2);
        const cropData = fullCtx.getImageData(cx, cy, cropSize, cropSize);
        code = jsQR(cropData.data, cropData.width, cropData.height, {
            inversionAttempts: 'attemptBoth'
        });
    }

    // Try 3: downscaled version — helps with high-res blurry cameras
    if (!code) {
        const scaleCanvas = document.createElement('canvas');
        scaleCanvas.width  = Math.round(vw * 0.5);
        scaleCanvas.height = Math.round(vh * 0.5);
        const scaleCtx = scaleCanvas.getContext('2d', { willReadFrequently: true });
        scaleCtx.drawImage(fullCanvas, 0, 0, scaleCanvas.width, scaleCanvas.height);
        const scaleData = scaleCtx.getImageData(0, 0, scaleCanvas.width, scaleCanvas.height);
        code = jsQR(scaleData.data, scaleData.width, scaleData.height, {
            inversionAttempts: 'attemptBoth'
        });
    }

    // Try 4: grayscale + threshold — helps in poor lighting
    if (!code) {
        const grayCanvas = document.createElement('canvas');
        grayCanvas.width  = vw;
        grayCanvas.height = vh;
        const grayCtx = grayCanvas.getContext('2d', { willReadFrequently: true });
        grayCtx.drawImage(video, 0, 0);
        const grayData = grayCtx.getImageData(0, 0, vw, vh);
        const d = grayData.data;
        for (let i = 0; i < d.length; i += 4) {
            const gray = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
            const bin  = gray > 128 ? 255 : 0;   // hard threshold binarize
            d[i] = d[i+1] = d[i+2] = bin;
        }
        grayCtx.putImageData(grayData, 0, 0);
        code = jsQR(grayData.data, vw, vh, { inversionAttempts: 'attemptBoth' });
    }

    if (!code) {
        setStatus('📷 Hold student QR card up to the camera', 'info');
        return;
    }

    // Extract token — QR encodes the full URL: .../attend/student/{token}
    const url   = code.data;
    const match = url.match(/\/attend\/student\/([A-Za-z0-9]+)$/);
    if (!match) {
        setStatus('❌ Unrecognised QR code', 'error');
        return;
    }

    const token = match[1];

    // Debounce — don't re-submit same token within cooldown
    if (token === lastQrToken) return;
    lastQrToken = token;

    // Flash QR frame green to give instant visual feedback
    const qrFrame = document.getElementById('qrFrame');
    if (qrFrame) {
        qrFrame.classList.add('detected');
        const hint = document.getElementById('qrHint');
        if (hint) hint.textContent = '✅ QR Detected!';
        setTimeout(() => {
            qrFrame.classList.remove('detected');
            if (hint) hint.textContent = 'Hold QR card here';
        }, 1200);
    }

    setStatus('⏳ QR detected — marking attendance…', 'wait');
    stopQrScan();
    inCooldown = true;

    try {
        const resp = await fetch(`/attend/student/${token}`, {
            method:  'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                'Accept':        'application/json',
            },
            body: JSON.stringify({
                session_id: SESSION_ID,
                camera_id:  CAMERA_ID,
                scan_type:  scanType,
            }),
        });

        const data = await resp.json();

        if (data.result === 'success') {
            playBeep('success');
            wrapper.className = 'camera-wrapper matched';
            const qrSnapshot = data.face_image || captureFrame();
            addToRoster(data.student_name, 'qr_code', data.arrived_at, data.scan_type, data.time_out, data.duration, qrSnapshot);
            const sub = data.scan_type === 'time_out'
                ? `QR Time Out · ${data.time_out} · stayed ${data.duration}`
                : `QR Time In · ${data.arrived_at}`;
            showMatchPopup(data.student_name, sub, data.scan_type);
            setStatus(`✅ ${data.student_name} — ${data.scan_type === 'time_out' ? 'Timed Out' : 'Timed In'} (QR)`, 'ok');
            if (data.student_id) markedIds.add(`${data.student_id}:${data.scan_type}`);
            await nextPersonCooldown(NEXT_PERSON_SECS);

        } else if (data.result === 'already_in') {
            playBeep('error');
            wrapper.className = 'camera-wrapper cooldown';
            setStatus(`ℹ️ ${data.student_name} already timed in — switch to Time-Out mode`, 'wait');
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 2000);
            lastQrToken = null;
            resumeAfter(2);

        } else if (data.result === 'cooldown') {
            playBeep('error');
            wrapper.className = 'camera-wrapper cooldown';
            setStatus(`ℹ️ ${data.student_name} already marked present`, 'wait');
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 1500);
            resumeAfter(2);

        } else {
            playBeep('error');
            wrapper.className = 'camera-wrapper no-match';
            setStatus('❌ ' + (data.message || 'QR not recognised'), 'error');
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 1200);
            lastQrToken = null;   // allow retry
            resumeAfter(2);
        }

    } catch (e) {
        setStatus('⚠️ Network error — ' + e.message, 'error');
        lastQrToken = null;
        resumeAfter(3);
    }
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
    if (scanMode === 'qr') return;   // QR mode — don't run face detection
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
            // Only show "no face" status in face recognition modes
            if (scanMode !== 'qr') {
                setStatus('🔍 No face detected — look at the camera', 'info');
            }
            updateConfidence(0);
            return;
        }

        // Match against registered faces
        const match      = faceMatcher.findBestMatch(detection.descriptor);
        const studentId  = match.label === 'unknown' ? null : parseInt(match.label, 10);
        const confidence = Math.round((1 - match.distance) * 100);
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

        // Already scanned for this type in this session? (client-side dedup)
        if (markedIds.has(`${studentId}:${scanType}`)) {
            setStatus(`ℹ️ ${info.name} already ${scanType === 'time_out' ? 'timed out' : 'timed in'} this session`, 'wait');
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
                scan_type:        scanType,
                confidence_score: confidence,
                face_image:       frame,
            }),
        });

        const data = await resp.json();

        if (data.result === 'success') {
            // Track this student+type so we don't scan them again this session
            markedIds.add(`${studentId}:${data.scan_type}`);
            playBeep('success');
            wrapper.className = 'camera-wrapper matched';
            addToRoster(data.student_name, 'face', data.arrived_at, data.scan_type, data.time_out, data.duration, frame);
            const sub = data.scan_type === 'time_out'
                ? `Time Out: ${data.time_out} · stayed ${data.duration}`
                : `Time In: ${data.arrived_at}`;
            showMatchPopup(data.student_name, sub, data.scan_type);
            setStatus(`✅ ${data.student_name} — ${data.scan_type === 'time_out' ? 'Timed Out' : 'Timed In'}`, 'ok');
            await nextPersonCooldown(NEXT_PERSON_SECS);

        } else if (data.result === 'already_in') {
            playBeep('error');
            wrapper.className = 'camera-wrapper cooldown';
            setStatus(`ℹ️ ${data.student_name} already timed in — switch to Time-Out mode`, 'wait');
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 2000);
            resumeAfter(2);

        } else if (data.result === 'already_out') {
            playBeep('error');
            wrapper.className = 'camera-wrapper cooldown';
            setStatus(`ℹ️ ${data.student_name} already timed out today`, 'wait');
            // Add to markedIds so scanner skips this student
            markedIds.add(`${studentId}:time_out`);
            setTimeout(() => { wrapper.className = 'camera-wrapper'; }, 2000);
            resumeAfter(2);

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
function showMatchPopup(name, sub, type) {
    document.getElementById('matchName').textContent = name;
    document.getElementById('matchSub').textContent  = sub;
    document.querySelector('#matchPopup .match-icon').textContent =
        type === 'time_out' ? '🏠' : '✅';
    matchPopup.style.display = 'block';
}

function hideMatchPopup() {
    matchPopup.style.display = 'none';
    wrapper.className = 'camera-wrapper';
}

async function nextPersonCooldown(secs) {
    // Stop both scan loops while showing popup
    stopAutoScan();
    stopQrScan();

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
            remaining > 0 ? `⏳ Next in ${remaining}s` : '⏳ Ready';
    }, 1000);

    await new Promise(r => setTimeout(r, secs * 1000));
    clearInterval(tick);
    hideMatchPopup();
    resumeAfter(0);
}

function resumeAfter(secs) {
    setTimeout(() => {
        inCooldown  = false;
        lastQrToken = null;   // allow next QR token
        wrapper.className = 'camera-wrapper';

        if (scanMode === 'auto' && IS_ACTIVE) {
            startAutoScan();
            setStatus('🔍 Auto-scanning — next person please', 'info');
        } else if (scanMode === 'qr' && IS_ACTIVE) {
            startQrScan();
            setStatus('📷 QR mode — hold next QR card up to the camera', 'info');
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
function addToRoster(name, method, timeIn, type, timeOut, duration, snapshot) {
    document.getElementById('emptyRoster')?.remove();
    rosterCount++;
    document.getElementById('rosterCount').textContent = rosterCount;

    const badgeClass = method === 'manual' ? 'badge-manual'
                     : method === 'qr_code' ? 'badge-qr'
                     : 'badge-face';
    const badgeLabel = method === 'manual' ? 'Manual'
                     : method === 'qr_code' ? 'QR'
                     : 'Face';

    const typePill = type === 'time_out'
        ? `<span style="font-size:10px;background:rgba(220,38,38,.15);color:#f87171;border-radius:5px;padding:2px 7px;font-weight:700;">OUT ${timeOut ?? ''}</span>`
        : `<span style="font-size:10px;background:rgba(22,163,74,.15);color:#4ade80;border-radius:5px;padding:2px 7px;font-weight:700;">IN ${timeIn}</span>`;

    const durLabel = duration ? `<span style="font-size:11px;color:#64748b;margin-left:4px;">${duration}</span>` : '';

    // Avatar — show captured snapshot if available, else gradient icon
    const avatarHtml = snapshot
        ? `<img src="${snapshot}" alt="${name}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,.15);">`
        : `<div class="roster-avatar">👤</div>`;

    const item = document.createElement('div');
    item.className = 'roster-item';
    item.innerHTML = `
        ${avatarHtml}
        <div style="flex:1;min-width:0;">
            <div class="roster-name">${name}</div>
            <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                ${typePill}${durLabel}
            </div>
        </div>
        <span class="roster-badge ${badgeClass}">${badgeLabel}</span>`;
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

// ── Live PH clock in camera view ──────────────────────────────────────────────
function updateCamClock() {
    const el = document.getElementById('camPhTime');
    if (!el) return;
    el.textContent = new Date().toLocaleTimeString('en-PH', {
        timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
    });
}
updateCamClock();
setInterval(updateCamClock, 1000);

// ── Warn only if leaving without intentionally going home ─────────────────────
let leavingIntentionally = false;
document.querySelector('a[href*="sessions/index"], a.cam-switch-btn')?.addEventListener('click', () => {
    leavingIntentionally = true;
});
// Stop camera stream cleanly when navigating away
window.addEventListener('beforeunload', () => {
    if (stream) stream.getTracks().forEach(t => t.stop());
});

// ═══════════════════════════════════════════════════════
//  QR CODE ATTENDANCE
// ═══════════════════════════════════════════════════════
const QR_URL = `${location.origin}/attend/qr/{{ $session->id }}/{{ hash('sha256', $session->id . config('app.key')) }}`;

function openQr() {
    const backdrop = document.getElementById('qrBackdrop');
    const input    = document.getElementById('qrLinkInput');
    if (!backdrop) return;

    input.value = QR_URL;

    // Generate QR into canvas
    QRCode.toCanvas(document.getElementById('qrCanvas'), QR_URL, {
        width: 200,
        margin: 1,
        color: { dark: '#000000', light: '#ffffff' }
    }, err => { if (err) console.error('QR error:', err); });

    backdrop.classList.add('open');
}

function closeQr() {
    document.getElementById('qrBackdrop')?.classList.remove('open');
}

function closeQrOutside(e) {
    if (e.target === document.getElementById('qrBackdrop')) closeQr();
}

function copyQrLink() {
    const input = document.getElementById('qrLinkInput');
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = input.nextElementSibling;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied!';
        setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy'; }, 2000);
    }).catch(() => { input.select(); document.execCommand('copy'); });
}

function downloadQr() {
    const canvas = document.getElementById('qrCanvas');
    const link   = document.createElement('a');
    link.download = `qr-attendance-{{ $session->subject }}-{{ $session->id }}.png`;
    link.href      = canvas.toDataURL('image/png');
    link.click();
}

// ── End Session confirmation ──
async function handleEndSession(btn) {
    const confirmed = await showConfirm({
        title:   'End Class Session',
        message: 'Are you sure you want to end this session for {{ $session->subject }} ({{ $session->section }})? The camera will be stopped and no more scans will be recorded.',
        okText:  'End Session',
        okType:  'danger',
        icon:    '🛑'
    });
    if (confirmed) {
        btn.closest('form').submit();
    }
}

// ══════════════════════════════════════════════════════
//  AUTO-END: check schedule every 30 seconds
// ══════════════════════════════════════════════════════
@if($session->isActive() && $session->scheduled_end)
const SCHEDULED_END = '{{ $session->scheduled_end }}'; // "HH:MM"
const CHECK_URL     = '{{ route('teacher.sessions.check-schedule', $session) }}';

function getMinutesLeft() {
    const now   = new Date();
    const [eh, em] = SCHEDULED_END.split(':').map(Number);
    const endMin = eh * 60 + em;
    const nowMin = now.getHours() * 60 + now.getMinutes();
    return endMin - nowMin;
}

function updateCountdown() {
    const el = document.getElementById('autoEndCountdown');
    if (!el) return;
    const mins = getMinutesLeft();
    if (mins > 0) {
        el.textContent = `(ends in ${mins}m)`;
        el.style.color = mins <= 5 ? '#f87171' : '#facc15';
    } else if (mins === 0) {
        el.textContent = '(ending now…)';
        el.style.color = '#f87171';
    } else {
        el.textContent = '(overdue)';
        el.style.color = '#f87171';
    }
}

async function checkAutoEnd() {
    try {
        const resp = await fetch(CHECK_URL, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        const data = await resp.json();

        if (data.auto_ended) {
            // Session auto-ended — stop scanning and notify teacher
            stopAutoScan();
            stopQrScan();
            inCooldown = true;
            if (scanBtn) scanBtn.disabled = true;

            setStatus('⏰ Session automatically ended — scheduled time reached', 'error');
            wrapper.className = 'camera-wrapper no-match';

            // Show floating alert then redirect
            const confirmed = await showConfirm({
                title:   'Session Auto-Ended',
                message: 'This session has reached its scheduled end time ({{ \Carbon\Carbon::parse($session->scheduled_end)->format('h:i A') }}) and has been automatically ended.',
                okText:  'Go to Sessions',
                okType:  'primary',
                icon:    '⏰'
            });
            window.location.href = '{{ route('teacher.sessions.index') }}';
        } else {
            updateCountdown();
        }
    } catch(e) {
        console.warn('Schedule check failed:', e);
    }
}

// Run immediately on load, then every 30 seconds
document.addEventListener('DOMContentLoaded', () => {
    updateCountdown();
    checkAutoEnd();
    setInterval(checkAutoEnd, 30000);
    setInterval(updateCountdown, 60000);
});
@endif
</script>
@endpush
