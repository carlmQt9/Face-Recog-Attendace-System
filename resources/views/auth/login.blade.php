<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — DMCMES Smart Attendance</title>
    <link rel="icon" type="image/png" href="{{ asset('dmcmes-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── DMCMES Theme Colors ──
           Navy    : #0c2d6b / #0c3d8a
           Gold    : #f5a800
           Green   : #1a6b3c
           Bg dark : #091a3e
        ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #091a3e;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        /* gold + green gradient orbs */
        body::before {
            content: '';
            position: fixed; top: -200px; right: -200px;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,168,0,.14) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed; bottom: -200px; left: -200px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(26,107,60,.15) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }

        /* ══ NAVBAR ══ */
        .top-nav {
            width: 100%;
            background: rgba(9,26,62,.92);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid #f5a800;
            padding: 10px 0;
            position: sticky; top: 0;
            z-index: 1000;
            flex-shrink: 0;
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .nav-brand img {
            width: 40px; height: 40px; object-fit: contain; border-radius: 8px;
        }
        .nav-brand-text { line-height: 1.15; }
        .nav-brand-abbr { font-size: 16px; font-weight: 900; color: #f5a800; letter-spacing: .04em; }
        .nav-brand-full { font-size: 10px; color: rgba(255,255,255,.55); display: block;
            font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
        .nav-links {
            display: flex; align-items: center; gap: 22px;
        }
        .nav-links a {
            font-size: 14px; font-weight: 500;
            color: #94a3b8; text-decoration: none;
            transition: color .2s;
        }
        .nav-links a:hover { color: #f5a800; }
        .nav-links .btn-nav {
            background: linear-gradient(135deg, #0c3d8a, #1a6b3c);
            color: #fff !important;
            border-radius: 9px; padding: 7px 20px;
            font-weight: 600;
            transition: opacity .2s, transform .2s;
        }
        .nav-links .btn-nav:hover { opacity: .88; transform: translateY(-1px); }
        @media (max-width: 540px) { .nav-hide-sm { display: none; } }

        /* ══ PAGE WRAP ══ */
        .page-wrap {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 48px 20px;
            width: 100%;
            position: relative; z-index: 1;
        }

        /* ══ TWO-PANEL CARD ══ */
        .login-card {
            display: flex;
            width: 100%; max-width: 880px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,.6);
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            flex: 0 0 44%;
            background: linear-gradient(155deg, #0c2d6b 0%, #091a3e 60%, #071430 100%);
            padding: 44px 36px;
            display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
            border-right: 2px solid rgba(245,168,0,.3);
        }
        /* decorative circles */
        .panel-left::before {
            content: '';
            position: absolute; top: -70px; right: -70px;
            width: 220px; height: 220px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,168,0,.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .panel-left::after {
            content: '';
            position: absolute; bottom: -60px; left: -60px;
            width: 200px; height: 200px; border-radius: 50%;
            background: radial-gradient(circle, rgba(26,107,60,.2) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Logo + school name */
        .brand-row {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 20px;
            position: relative; z-index: 1;
        }
        .brand-logo-wrap {
            width: 66px; height: 66px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.1);
            border: 2px solid rgba(245,168,0,.4);
            flex-shrink: 0;
            padding: 6px;
        }
        .brand-logo-wrap img { width: 100%; height: 100%; object-fit: contain; }
        .brand-school { line-height: 1.25; }
        .brand-school .abbr  { font-size: 22px; font-weight: 900; color: #f5a800; }
        .brand-school .full  { font-size: 10px; color: rgba(255,255,255,.55);
            text-transform: uppercase; letter-spacing: .05em; display: block; margin-top: 2px; }

        .brand-tagline {
            font-size: 13px; color: #94a3b8;
            line-height: 1.65; margin-bottom: 28px;
            position: relative; z-index: 1;
        }
        .brand-tagline strong { color: #f5a800; }

        /* Dedication badge */
        .dedication-badge {
            background: rgba(245,168,0,.12);
            border: 1px solid rgba(245,168,0,.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11px; color: rgba(255,255,255,.8);
            position: relative; z-index: 1;
            margin-bottom: 24px;
        }
        .dedication-badge .ded-title {
            font-size: 9px; font-weight: 700;
            color: #f5a800; text-transform: uppercase;
            letter-spacing: .1em; margin-bottom: 3px;
        }
        .dedication-badge .ded-name {
            font-size: 12px; font-weight: 700; color: #fff;
        }

        .left-stats {
            display: flex; gap: 24px;
            position: relative; z-index: 1;
        }
        .left-stat .num { font-size: 22px; font-weight: 800; color: #f5a800; }
        .left-stat .lbl { font-size: 11px; color: #64748b; font-weight: 500; margin-top: 2px; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            background: rgba(255,255,255,.97);
            padding: 48px 44px 40px;
        }
        .welcome-title {
            font-size: 26px; font-weight: 800;
            color: #0c2d6b; margin-bottom: 4px;
        }
        .welcome-sub {
            font-size: 13px; color: #64748b;
            margin-bottom: 28px;
        }

        /* Form */
        .form-label {
            font-size: 11px; font-weight: 700;
            letter-spacing: .6px; text-transform: uppercase;
            color: #475569; margin-bottom: 6px;
        }
        .input-group-text {
            background: #f8fafc; border-color: #e2e8f0; color: #0c3d8a;
        }
        .form-control {
            border-color: #e2e8f0; font-size: 14px;
            color: #0f172a; background: #f8fafc;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        input::-webkit-password-toggle-button { display: none !important; }
        .form-control:focus {
            border-color: #0c3d8a;
            box-shadow: 0 0 0 3px rgba(12,61,138,.12);
            background: #fff;
        }

        .btn-signin {
            background: linear-gradient(135deg, #0c3d8a, #1a6b3c);
            border: none; color: #fff; font-weight: 700;
            font-size: 15px; padding: 13px; border-radius: 12px;
            width: 100%; cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 6px 20px rgba(12,61,138,.35);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-signin:hover   { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(12,61,138,.45); }
        .btn-signin:active  { transform: scale(.98); }
        .btn-signin:disabled{ opacity: .6; cursor: not-allowed; }

        .alert-err {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #991b1b; border-radius: 10px;
            padding: 10px 14px; font-size: 13px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        .foot { text-align: center; margin-top: 20px; }

        .role-pills { display: flex; gap: 6px; justify-content: center; margin-top: 10px; }
        .role-pill  { font-size: 11px; font-weight: 600; border-radius: 50px; padding: 3px 10px; }
        .rp-admin   { background: #e0e7ff; color: #0c3d8a; }
        .rp-teacher { background: #fef3c7; color: #92400e; }
        .rp-student { background: #dcfce7; color: #166534; }

        /* school footer stripe */
        .school-footer {
            width: 100%;
            text-align: center;
            padding: 12px 0 18px;
            font-size: 11px; color: rgba(255,255,255,.35);
            position: relative; z-index: 1;
        }
        .school-footer span { color: #f5a800; }

        /* Responsive */
        @media (max-width: 680px) {
            .login-card { flex-direction: column; max-width: 420px; }
            .panel-left { padding: 32px 28px; }
            .left-stats { justify-content: center; }
            .panel-right { padding: 32px 28px 28px; }
        }
    </style>
</head>
<body>

{{-- redirect if already logged in --}}
@auth
<script>
    window.location.href = "@php
        echo match(auth()->user()->role) {
            'admin'   => route('admin.dashboard'),
            'teacher' => route('teacher.sessions.index'),
            'student' => route('student.attendance.index'),
            default   => '/'
        };
    @endphp";
</script>
@endauth

{{-- ══ NAVBAR ══ --}}
<nav class="top-nav">
    <div class="nav-inner">
        <a href="{{ route('landing') }}" class="nav-brand">
                    <img src="<?php echo 'data:'.mime_content_type(public_path('dmcmes-logo.png')).';base64,'.base64_encode(file_get_contents(public_path('dmcmes-logo.png'))); ?>" alt="DMCMES Logo">
            <div class="nav-brand-text">
                <div class="nav-brand-abbr">DMCMES</div>
                <span class="nav-brand-full">Smart Attendance System</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="{{ route('landing') }}" class="nav-hide-sm">Home</a>
            <a href="{{ route('landing') }}#features" class="nav-hide-sm">Features</a>
            <a href="{{ route('landing') }}#how" class="nav-hide-sm">How It Works</a>
            <a href="{{ route('login') }}" class="btn-nav">Sign In</a>
        </div>
    </div>
</nav>

{{-- ══ CARD ══ --}}
<div class="page-wrap">
    <div class="login-card">

        {{-- ── LEFT PANEL ── --}}
        <div class="panel-left">
            <div class="brand-row">
                <div class="brand-logo-wrap">
                    <img src="<?php echo 'data:'.mime_content_type(public_path('dmcmes-logo.png')).';base64,'.base64_encode(file_get_contents(public_path('dmcmes-logo.png'))); ?>" alt="DMCMES">
                </div>                <div class="brand-school">
                    <div class="abbr">DMCMES</div>
                    <span class="full">Don Marcelo C. Marty<br>Elementary School</span>
                </div>
            </div>

            <div class="dedication-badge">
                <div class="ded-title">🏫 Dedicated to</div>
                <div class="ded-name">Don Marcelo C. Marty Elementary School</div>
                <div style="font-size:10px;color:rgba(255,255,255,.5);margin-top:3px;">
                    Sta. Cruz, Zambales &nbsp;·&nbsp; DepEd NCR
                </div>
            </div>

            <div class="brand-tagline">
                <strong>Smart Face Recognition Attendance</strong> — mark students present in under a second, and instantly notify parents.
            </div>

            <div class="left-stats">
                <div class="left-stat">
                    <div class="num">3</div>
                    <div class="lbl">Roles</div>
                </div>
                <div class="left-stat">
                    <div class="num">&lt;1s</div>
                    <div class="lbl">Recognition</div>
                </div>
                <div class="left-stat">
                    <div class="num">Live</div>
                    <div class="lbl">Tracking</div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL ── --}}
        <div class="panel-right">

            <div class="welcome-title">Welcome Back</div>
            <div class="welcome-sub">Sign in to your DMCMES portal</div>

            @if($errors->any())
                <div class="alert-err">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" placeholder="you@school.edu"
                               required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="pwdField"
                               class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="input-group-text" style="cursor:pointer;"
                                onclick="togglePwd()" title="Show/hide password">
                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember"
                               style="font-size:13px;color:#64748b;">Keep me logged in</label>
                    </div>
                </div>

                <button type="submit" class="btn-signin" id="signInBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="foot">
                <div class="role-pills mt-2">
                    <span class="role-pill rp-admin">Admin</span>
                    <span class="role-pill rp-teacher">Teacher</span>
                    <span class="role-pill rp-student">Student</span>
                </div>
            </div>

        </div>{{-- /panel-right --}}
    </div>{{-- /login-card --}}
</div>{{-- /page-wrap --}}

<div class="school-footer">
    © {{ date('Y') }} <span>DMCMES</span> — Don Marcelo C. Marty Elementary School &nbsp;·&nbsp; Smart Attendance System
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye';
    } else {
        f.type = 'password';
        i.className = 'bi bi-eye-slash';
    }
}
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('signInBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
});
</script>
</body>
</html>
