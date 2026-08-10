<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DMCMES — Smart School Attendance System</title>
<link rel="icon" type="image/png" href="{{ asset('donma logo.png?v=' . time()) }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #0c3d8a;
    --primary-light: #4a7fd4;
    --accent: #f5a800;
    --dark: #0f172a;
    --dark2: #1e293b;
    --card-bg: rgba(255,255,255,0.04);
    --border: rgba(255,255,255,0.08);
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior: smooth; }
body { font-family:'Inter',sans-serif; background:var(--dark); color:#e2e8f0; overflow-x:hidden; }

/* NAVBAR */
.navbar { background:rgba(15,23,42,0.88); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:14px 0; position:sticky; top:0; z-index:1000; transition:background .3s; }
.navbar-brand { font-weight:800; font-size:22px; color:#fff !important; display:flex; align-items:center; gap:10px; }
.brand-dot { width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.nav-link { color:#94a3b8 !important; font-weight:500; transition:color .2s; }
.nav-link:hover { color:#fff !important; }
.btn-nav { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff !important; border-radius:10px; padding:8px 22px; font-weight:600; border:none; transition:opacity .2s,transform .2s; text-decoration:none; }
.btn-nav:hover { opacity:.9; transform:translateY(-1px); }

/* HERO */
.hero { min-height:100vh; display:flex; align-items:center; position:relative; overflow:hidden; padding:60px 0 60px; }
.hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse 80% 60% at 60% 40%,rgba(12,61,138,.25) 0%,transparent 70%),radial-gradient(ellipse 60% 50% at 20% 80%,rgba(245,168,0,.18) 0%,transparent 60%),var(--dark); }
.hero-grid { position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px); background-size:60px 60px; }
.hero-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(12,61,138,.15); border:1px solid rgba(12,61,138,.4); border-radius:50px; padding:6px 16px; font-size:13px; font-weight:600; color:var(--primary-light); margin-bottom:28px; }
.hero-badge .dot { width:8px; height:8px; background:#4ade80; border-radius:50%; animation:pulseDot 2s infinite; }
@keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
.hero h1 { font-size:clamp(40px,6vw,72px); font-weight:900; line-height:1.08; letter-spacing:-2px; color:#fff; margin-bottom:24px; }
.gradient-text { background:linear-gradient(135deg,var(--primary-light),var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.hero p.lead { font-size:18px; color:#94a3b8; line-height:1.7; max-width:520px; margin-bottom:40px; }
.btn-primary-hero { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border:none; border-radius:14px; padding:16px 36px; font-size:16px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:10px; transition:transform .2s,box-shadow .2s; box-shadow:0 8px 32px rgba(12,61,138,.4); }
.btn-primary-hero:hover { transform:translateY(-3px); box-shadow:0 16px 40px rgba(12,61,138,.5); color:#fff; }
.btn-secondary-hero { background:transparent; color:#e2e8f0; border:1px solid var(--border); border-radius:14px; padding:16px 36px; font-size:16px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:10px; transition:background .2s,border-color .2s; }
.btn-secondary-hero:hover { background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.2); color:#fff; }
</style>
</head>
<body>

<style>
/* SCAN MOCKUP */
.face-mockup { width:100%; max-width:480px; margin:0 auto; }
.scan-frame { background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:24px; padding:28px; position:relative; overflow:hidden; }
.scan-frame::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(12,61,138,.08),rgba(245,168,0,.08)); pointer-events:none; }
.camera-view { background:#0a0a1a; border-radius:16px; aspect-ratio:4/3; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.face-icon { font-size:80px; color:rgba(12,61,138,.4); animation:floatY 3s ease-in-out infinite; }
@keyframes floatY { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
.scan-line { position:absolute; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,var(--accent),transparent); animation:scanMove 2.5s linear infinite; box-shadow:0 0 12px var(--accent); }
@keyframes scanMove { 0%{top:10%} 100%{top:90%} }
.corner { position:absolute; width:24px; height:24px; }
.c-tl { top:12px; left:12px; border-top:3px solid var(--accent); border-left:3px solid var(--accent); border-radius:4px 0 0 0; }
.c-tr { top:12px; right:12px; border-top:3px solid var(--accent); border-right:3px solid var(--accent); border-radius:0 4px 0 0; }
.c-bl { bottom:12px; left:12px; border-bottom:3px solid var(--accent); border-left:3px solid var(--accent); border-radius:0 0 0 4px; }
.c-br { bottom:12px; right:12px; border-bottom:3px solid var(--accent); border-right:3px solid var(--accent); border-radius:0 0 4px 0; }
.scan-status { display:flex; align-items:center; justify-content:space-between; margin-top:16px; }
.status-pill { display:flex; align-items:center; gap:8px; background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.3); border-radius:50px; padding:6px 14px; font-size:13px; font-weight:600; color:#4ade80; }
.status-pill .pulse { width:8px; height:8px; background:#4ade80; border-radius:50%; animation:pulseDot 1.5s infinite; }
.notify-toast { background:rgba(15,23,42,.95); border:1px solid rgba(74,222,128,.3); border-radius:14px; padding:12px 16px; display:flex; align-items:center; gap:12px; font-size:13px; margin-top:16px; box-shadow:0 8px 32px rgba(0,0,0,.4); }
.toast-ico { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#4ade80,#f5a800); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.toast-name { font-weight:700; color:#fff; font-size:14px; }
.toast-sub { color:#64748b; font-size:12px; }

/* STATS BAR */
.stats-bar { background:rgba(255,255,255,.03); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:40px 0; }
.stat-num { font-size:38px; font-weight:900; background:linear-gradient(135deg,var(--primary-light),var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.stat-lbl { color:#64748b; font-size:14px; font-weight:500; margin-top:4px; }

/* SECTIONS */
section { padding:100px 0; }
.section-label { font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--primary-light); margin-bottom:14px; }
.section-title { font-size:clamp(30px,4vw,48px); font-weight:800; color:#fff; line-height:1.15; letter-spacing:-1px; margin-bottom:14px; }
.section-sub { color:#64748b; font-size:17px; line-height:1.7; max-width:520px; }

/* FEATURE CARDS */
.feat-card { background:var(--card-bg); border:1px solid var(--border); border-radius:20px; padding:28px; height:100%; transition:transform .3s,border-color .3s,box-shadow .3s; position:relative; overflow:hidden; }
.feat-card:hover { transform:translateY(-6px); border-color:rgba(12,61,138,.4); box-shadow:0 20px 60px rgba(12,61,138,.15); }
.feat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(12,61,138,.5),transparent); opacity:0; transition:opacity .3s; }
.feat-card:hover::before { opacity:1; }
.feat-ico { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:20px; }
.ico-purple { background:rgba(12,61,138,.15); color:var(--primary-light); }
.ico-cyan   { background:rgba(245,168,0,.15); color:var(--accent); }
.ico-green  { background:rgba(74,222,128,.15); color:#4ade80; }
.ico-orange { background:rgba(251,146,60,.15); color:#fb923c; }
.ico-pink   { background:rgba(244,114,182,.15); color:#f472b6; }
.ico-yellow { background:rgba(250,204,21,.15); color:#facc15; }
.feat-card h5 { font-size:17px; font-weight:700; color:#fff; margin-bottom:10px; }
.feat-card p  { color:#64748b; font-size:14px; line-height:1.7; margin:0; }
</style>

<style>
/* HOW IT WORKS */
.how-section { background:rgba(255,255,255,.02); }
.step-card { text-align:center; padding:20px 12px; }
.step-num { width:64px; height:64px; background:linear-gradient(135deg,var(--primary),var(--accent)); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:900; color:#fff; margin:0 auto 20px; box-shadow:0 8px 24px rgba(12,61,138,.4); }
.step-card h5 { font-size:18px; font-weight:700; color:#fff; margin-bottom:10px; }
.step-card p  { color:#64748b; font-size:14px; line-height:1.7; }

/* ROLE CARDS */
.role-card { background:var(--card-bg); border:1px solid var(--border); border-radius:24px; padding:36px; height:100%; transition:transform .3s,box-shadow .3s; }
.role-card:hover { transform:translateY(-6px); box-shadow:0 24px 64px rgba(0,0,0,.3); }
.role-header { display:flex; align-items:center; gap:16px; margin-bottom:28px; }
.role-avatar { width:56px; height:56px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
.avatar-admin   { background:linear-gradient(135deg,#7c3aed,#0c3d8a); }
.avatar-teacher { background:linear-gradient(135deg,#0c3d8a,#f5a800); }
.avatar-student { background:linear-gradient(135deg,#059669,#10b981); }
.role-name { font-size:20px; font-weight:800; color:#fff; }
.role-tag  { font-size:12px; color:#64748b; font-weight:500; margin-top:2px; }
.role-feat { display:flex; align-items:flex-start; gap:12px; margin-bottom:14px; font-size:14px; color:#94a3b8; line-height:1.5; }
.role-feat i { color:#4ade80; margin-top:2px; flex-shrink:0; }
.btn-role { display:inline-flex; align-items:center; gap:6px; border-radius:10px; padding:8px 20px; font-size:13px; font-weight:600; text-decoration:none; margin-top:20px; transition:opacity .2s; }
.btn-role:hover { opacity:.8; }
.btn-role-admin   { background:rgba(12,61,138,.15); color:var(--primary-light); border:1px solid rgba(12,61,138,.3); }
.btn-role-teacher { background:rgba(245,168,0,.12); color:var(--accent); border:1px solid rgba(245,168,0,.3); }
.btn-role-student { background:rgba(74,222,128,.12); color:#4ade80; border:1px solid rgba(74,222,128,.3); }

/* EMAIL PREVIEW */
.email-card { background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 32px 80px rgba(0,0,0,.5); max-width:460px; margin:0 auto; }
.email-top  { background:linear-gradient(135deg,#0c3d8a,#1a6b3c); padding:20px 24px; color:#fff; font-size:15px; font-weight:700; }
.email-body { padding:24px; }
.email-body p { color:#374151; font-size:14px; line-height:1.7; margin-bottom:12px; }
.info-row { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f9fafb; border-radius:10px; margin-bottom:8px; font-size:13px; }
.info-row .k { color:#6b7280; font-weight:500; }
.info-row .v { color:#111827; font-weight:700; }
.e-badge { display:inline-block; background:#dcfce7; color:#166534; border-radius:20px; padding:4px 14px; font-size:13px; font-weight:700; }

/* CTA */
.cta-section { background:linear-gradient(135deg,rgba(12,61,138,.2),rgba(245,168,0,.15)); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:100px 0; text-align:center; }
.cta-section h2 { font-size:clamp(30px,4vw,52px); font-weight:900; color:#fff; letter-spacing:-1.5px; margin-bottom:20px; }
.cta-section p  { color:#94a3b8; font-size:18px; margin-bottom:40px; }

/* FOOTER */
footer { background:var(--dark2); border-top:1px solid var(--border); padding:56px 0 32px; }
.foot-logo { font-size:20px; font-weight:800; color:#fff; display:flex; align-items:center; gap:10px; margin-bottom:12px; }
footer p { color:#475569; font-size:14px; line-height:1.6; }
.foot-col-title { font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
.foot-link { color:#64748b; text-decoration:none; font-size:14px; display:block; margin-bottom:10px; transition:color .2s; }
.foot-link:hover { color:#fff; }
.foot-hr { border-color:var(--border); margin:32px 0 24px; }
.foot-copy { color:#334155; font-size:13px; }

/* ANIMATIONS */
.fade-up { opacity:0; transform:translateY(30px); transition:opacity .7s ease,transform .7s ease; }
.fade-up.visible { opacity:1; transform:translateY(0); }
.d1{transition-delay:.1s} .d2{transition-delay:.2s} .d3{transition-delay:.3s}
.d4{transition-delay:.4s} .d5{transition-delay:.5s} .d6{transition-delay:.6s}

::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:var(--dark)}
::-webkit-scrollbar-thumb{background:#334155;border-radius:3px}
</style>

<!-- ══════════ NAVBAR ══════════ -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#" style="display:flex;align-items:center;gap:12px;">
      <img src="{{ asset('donma logo.png?v=' . time()) }}" alt="DMCMES Logo" style="width:44px;height:44px;object-fit:contain;border-radius:6px;">
      <div style="display:flex;flex-direction:column;line-height:1.1;">
        <span style="font-size:16px;font-weight:900;color:#f5a800;letter-spacing:0.05em;">DMCMES</span>
        <span style="font-size:10px;color:rgba(255,255,255,0.6);font-weight:500;text-transform:uppercase;letter-spacing:0.05em;">Smart Attendance</span>
      </div>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav" style="color:#94a3b8">
      <i class="bi bi-list fs-4"></i>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
        <li class="nav-item"><a class="nav-link" href="#roles">For Schools</a></li>
        <li class="nav-item"><a class="nav-link" href="#notify">Notifications</a></li>
      </ul>
      <div class="d-flex gap-3 align-items-center mt-3 mt-lg-0">
        <a href="{{ route('login') }}" class="nav-link">Sign In</a>
        <a href="{{ route('login') }}" class="btn-nav">Get Started</a>
      </div>
    </div>
  </div>
</nav>

<!-- ══════════ HERO ══════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge fade-up"><span class="dot"></span> AI-Powered School Attendance</div>
        <h1 class="fade-up d1">Attendance in<br><span class="gradient-text">One Glance.</span><br>Zero Effort.</h1>
        <p class="lead fade-up d2">Students walk in, look at the camera, and they're marked present — instantly. No cards, no apps, no delays. Parents get notified in under a second.</p>
        <div class="d-flex flex-wrap gap-3 fade-up d3">
          <a href="{{ route('login') }}" class="btn-primary-hero"><i class="bi bi-play-circle-fill"></i> Launch System</a>
          <a href="#how" class="btn-secondary-hero"><i class="bi bi-arrow-down-circle"></i> See How It Works</a>
        </div>
      </div>
      <div class="col-lg-6 fade-up d4">
        <div class="face-mockup">
          <div class="scan-frame">
            <div class="camera-view">
              <i class="bi bi-person-bounding-box face-icon"></i>
              <div class="scan-line"></div>
              <div class="corner c-tl"></div><div class="corner c-tr"></div>
              <div class="corner c-bl"></div><div class="corner c-br"></div>
            </div>
            <div class="scan-status">
              <div class="status-pill"><span class="pulse"></span> Camera Active</div>
              <span style="font-size:13px;color:#475569">Classroom 101</span>
            </div>
          </div>
          <div class="notify-toast">
            <div class="toast-ico">✅</div>
            <div>
              <div class="toast-name">John Dela Cruz — Present</div>
              <div class="toast-sub">📧 Parent notified · just now · 8:07 AM</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ STATS ══════════ -->
<div class="stats-bar">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3 fade-up"><div class="stat-num">&lt;1s</div><div class="stat-lbl">Recognition Speed</div></div>
      <div class="col-6 col-md-3 fade-up d1"><div class="stat-num">99.7%</div><div class="stat-lbl">Accuracy Rate</div></div>
      <div class="col-6 col-md-3 fade-up d2"><div class="stat-num">3</div><div class="stat-lbl">User Roles</div></div>
      <div class="col-6 col-md-3 fade-up d3"><div class="stat-num">0</div><div class="stat-lbl">Physical ID Cards Needed</div></div>
    </div>
  </div>
</div>

<!-- ══════════ FEATURES ══════════ -->
<section id="features">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label fade-up">Core Features</div>
      <h2 class="section-title fade-up d1">Everything your school needs</h2>
      <p class="section-sub mx-auto fade-up d2">A complete, end-to-end attendance solution — from face registration to parent notifications.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 fade-up">
        <div class="feat-card">
          <div class="feat-ico ico-purple"><i class="bi bi-person-bounding-box"></i></div>
          <h5>Face Registration</h5>
          <p>Capture student and teacher faces directly from a webcam. Faces are securely mapped and stored for instant recognition.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 fade-up d1">
        <div class="feat-card">
          <div class="feat-ico ico-cyan"><i class="bi bi-lightning-charge-fill"></i></div>
          <h5>Instant Recognition</h5>
          <p>Students simply look at the camera for one second. The system identifies them and marks attendance automatically.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 fade-up d2">
        <div class="feat-card">
          <div class="feat-ico ico-green"><i class="bi bi-envelope-check-fill"></i></div>
          <h5>Parent Notifications</h5>
          <p>The moment a student is recognized, their parent receives a Gmail notification with arrival time and location.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 fade-up d3">
        <div class="feat-card">
          <div class="feat-ico ico-orange"><i class="bi bi-camera-video-fill"></i></div>
          <h5>Multi-Camera Support</h5>
          <p>Manage cameras across classrooms, school entrances, and kiosk stations from one admin panel.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 fade-up d4">
        <div class="feat-card">
          <div class="feat-ico ico-pink"><i class="bi bi-hand-index-fill"></i></div>
          <h5>Manual Override</h5>
          <p>Teachers can manually mark students present if face recognition fails due to injury, glasses, or poor lighting.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 fade-up d5">
        <div class="feat-card">
          <div class="feat-ico ico-yellow"><i class="bi bi-download"></i></div>
          <h5>Attendance Archive</h5>
          <p>Admins can view, filter, print, and export master attendance sheets as CSV for the entire school.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ HOW IT WORKS ══════════ -->
<section id="how" class="how-section">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label fade-up">Process</div>
      <h2 class="section-title fade-up d1">How It Works</h2>
      <p class="section-sub mx-auto fade-up d2">From the moment a student walks in, to the parent's inbox — it all happens in seconds.</p>
    </div>
    <div class="row g-4">
      <div class="col-6 col-md-3 fade-up">
        <div class="step-card">
          <div class="step-num">1</div>
          <h5>Register Face</h5>
          <p>Admin takes a quick webcam photo of each student or teacher to save their facial data.</p>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-up d1">
        <div class="step-card">
          <div class="step-num">2</div>
          <h5>Walk &amp; Look</h5>
          <p>Students approach the camera, look at it for one second — no touching or swiping required.</p>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-up d2">
        <div class="step-card">
          <div class="step-num">3</div>
          <h5>Instant Beep</h5>
          <p>1 happy beep means success. 2 sharp beeps means an error. Clear audio feedback every time.</p>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-up d3">
        <div class="step-card">
          <div class="step-num">4</div>
          <h5>Parent Notified</h5>
          <p>An email is sent to the registered Gmail with the student's name, location, and arrival time.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ ROLES ══════════ -->
<section id="roles">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label fade-up">Role-Based Access</div>
      <h2 class="section-title fade-up d1">Built for everyone in school</h2>
      <p class="section-sub mx-auto fade-up d2">Three dedicated portals, each designed for the exact needs of that role.</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-4 fade-up">
        <div class="role-card">
          <div class="role-header">
            <div class="role-avatar avatar-admin">🛡️</div>
            <div><div class="role-name">Admin</div><div class="role-tag">The School Office</div></div>
          </div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Register student &amp; teacher faces via webcam</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Link parent Gmail to student profiles</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Activate or deactivate cameras by room</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Set cool-down timer &amp; speaker volume</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Download attendance archive as CSV</div>
          <a href="{{ route('login') }}" class="btn-role btn-role-admin">Admin Login <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 fade-up d1">
        <div class="role-card">
          <div class="role-header">
            <div class="role-avatar avatar-teacher">👩‍🏫</div>
            <div><div class="role-name">Teacher</div><div class="role-tag">The Classroom Leader</div></div>
          </div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Start &amp; stop class session tracking</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Live roster auto-updates as students arrive</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Listen to audio beeps for hands-free monitoring</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Manually mark a student present with one click</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>View session history and attendance counts</div>
          <a href="{{ route('login') }}" class="btn-role btn-role-teacher">Teacher Login <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 fade-up d2">
        <div class="role-card">
          <div class="role-header">
            <div class="role-avatar avatar-student">🎒</div>
            <div><div class="role-name">Student</div><div class="role-tag">The Kids' Experience</div></div>
          </div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Look at the camera — completely hands-free</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>1 happy beep confirms attendance is logged</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>2 sharp beeps signal an error or cool-down</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>Parents notified automatically via Gmail</div>
          <div class="role-feat"><i class="bi bi-check-circle-fill"></i>View personal calendar of arrival history</div>
          <a href="{{ route('login') }}" class="btn-role btn-role-student">Student Login <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ NOTIFICATIONS ══════════ -->
<section id="notify" style="background:rgba(255,255,255,.015);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 fade-up">
        <div class="section-label">Automated Alerts</div>
        <h2 class="section-title">Parents always<br>in the loop</h2>
        <p class="section-sub">The moment a student is scanned, their parent receives a detailed email — with the student's name, location, and exact arrival time. Under one second, every time.</p>
        <div class="d-flex flex-column gap-3 mt-4">
          <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;background:rgba(74,222,128,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#4ade80;flex-shrink:0;font-size:18px;"><i class="bi bi-send-check-fill"></i></div>
            <div><div style="color:#fff;font-weight:600;font-size:14px;">Instant delivery</div><div style="color:#475569;font-size:13px;">Email sent in under 1 second after scan</div></div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;background:rgba(12,61,138,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--primary-light);flex-shrink:0;font-size:18px;"><i class="bi bi-google"></i></div>
            <div><div style="color:#fff;font-weight:600;font-size:14px;">Gmail integration</div><div style="color:#475569;font-size:13px;">Works with any parent Gmail account</div></div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;background:rgba(245,168,0,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;font-size:18px;"><i class="bi bi-calendar-event-fill"></i></div>
            <div><div style="color:#fff;font-weight:600;font-size:14px;">Date, time &amp; location</div><div style="color:#475569;font-size:13px;">Full details in every notification</div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-7 fade-up d2">
        <div class="email-card">
          <div class="email-top">📋 Attendance Alert: John Dela Cruz has arrived safely</div>
          <div class="email-body">
            <p>Hi <strong>Maria Cruz</strong>,</p>
            <p>This is an automated update from the school attendance system.</p>
            <div class="info-row"><span class="k">Student</span><span class="v">John Dela Cruz</span></div>
            <div class="info-row"><span class="k">Location</span><span class="v">Classroom 101 Camera</span></div>
            <div class="info-row"><span class="k">Date</span><span class="v">{{ now()->format('F j, Y') }}</span></div>
            <div class="info-row"><span class="k">Arrival Time</span><span class="v">8:05 AM</span></div>
            <div class="info-row"><span class="k">Status</span><span class="v"><span class="e-badge">✅ Present</span></span></div>
            <p class="mt-3">Have a great day!</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:12px;">This is an automated message. Please do not reply.<br><strong>School Face Recognition Attendance System</strong></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ CTA ══════════ -->
<section class="cta-section">
  <div class="container fade-up">
    <h2>Ready to modernize your school?</h2>
    <p>Set up in minutes. No hardware beyond a camera. No apps for students.</p>
    <a href="{{ route('login') }}" class="btn-primary-hero" style="font-size:17px;padding:18px 44px;margin:0 auto;">
      <i class="bi bi-rocket-takeoff-fill"></i> Launch the System
    </a>
  </div>
</section>

<!-- ══════════ FOOTER ══════════ -->
<footer>
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="foot-logo" style="display:flex;align-items:center;gap:12px;">
          <img src="{{ asset('donma logo.png?v=' . time()) }}" alt="DMCMES Logo" style="width:40px;height:40px;object-fit:contain;border-radius:6px;">
          <span>DMCMES</span>
        </div>
        <p>A modern, AI-powered face recognition attendance system built for schools. Fast, secure, and completely hands-free.</p>
      </div>
      <div class="col-6 col-lg-2">
        <div class="foot-col-title">System</div>
        <a href="#features" class="foot-link">Features</a>
        <a href="#how" class="foot-link">How It Works</a>
        <a href="#roles" class="foot-link">Roles</a>
        <a href="#notify" class="foot-link">Notifications</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="foot-col-title">Portals</div>
        <a href="{{ route('login') }}" class="foot-link">Admin Login</a>
        <a href="{{ route('login') }}" class="foot-link">Teacher Login</a>
        <a href="{{ route('login') }}" class="foot-link">Student Login</a>
      </div>
      <div class="col-lg-4">
        <div class="foot-col-title">Contact &amp; Support</div>
        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-items-center gap-2" style="font-size:14px;color:#64748b;">
            <i class="bi bi-envelope-fill" style="color:var(--primary-light);font-size:15px;"></i>
            <span>support@faceattend.edu</span>
          </div>
          <div class="d-flex align-items-center gap-2" style="font-size:14px;color:#64748b;">
            <i class="bi bi-telephone-fill" style="color:var(--primary-light);font-size:15px;"></i>
            <span>+63 (02) 8123-4567</span>
          </div>
          <div class="d-flex align-items-center gap-2" style="font-size:14px;color:#64748b;">
            <i class="bi bi-geo-alt-fill" style="color:var(--primary-light);font-size:15px;"></i>
            <span>School IT Office, Main Building</span>
          </div>
          <div class="d-flex align-items-center gap-2 mt-1" style="font-size:14px;color:#64748b;">
            <i class="bi bi-clock-fill" style="color:var(--primary-light);font-size:15px;"></i>
            <span>Mon – Fri, 7:00 AM – 5:00 PM</span>
          </div>
        </div>
      </div>
    </div>
    <hr class="foot-hr">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <p class="foot-copy mb-0">© {{ date('Y') }} DMCMES — Built with Laravel 12.</p>
      <div class="d-flex gap-3">
        <span style="color:#334155;font-size:13px;"><i class="bi bi-shield-check me-1"></i>Secure</span>
        <span style="color:#334155;font-size:13px;"><i class="bi bi-lightning-charge me-1"></i>Fast</span>
        <span style="color:#334155;font-size:13px;"><i class="bi bi-phone me-1"></i>Responsive</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fade-up on scroll
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });
document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', function(e) {
    const t = document.querySelector(this.getAttribute('href'));
    if (t) { e.preventDefault(); t.scrollIntoView({ behavior:'smooth', block:'start' }); }
  });
});

// Sticky navbar tint
window.addEventListener('scroll', () => {
  document.querySelector('.navbar').style.background =
    window.scrollY > 40 ? 'rgba(15,23,42,0.98)' : 'rgba(15,23,42,0.88)';
});
</script>
</body>
</html>
