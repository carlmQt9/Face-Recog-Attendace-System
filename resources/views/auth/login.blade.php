<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Face Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        /* gradient orbs */
        body::before {
            content: '';
            position: fixed; top: -200px; right: -200px;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(79,70,229,.18) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed; bottom: -200px; left: -200px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(6,182,212,.12) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }

        /* ══ NAVBAR ══ */
        .top-nav {
            width: 100%;
            background: rgba(15,23,42,.88);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,.08);
            padding: 13px 0;
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
        .nav-brand-dot {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
        }
        .nav-brand-name {
            font-size: 18px; font-weight: 800; color: #fff;
        }
        .nav-brand-name span { color: #06b6d4; }
        .nav-links {
            display: flex; align-items: center; gap: 22px;
        }
        .nav-links a {
            font-size: 14px; font-weight: 500;
            color: #94a3b8; text-decoration: none;
            transition: color .2s;
        }
        .nav-links a:hover { color: #fff; }
        .nav-links .btn-nav {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: #fff !important;
            border-radius: 9px; padding: 7px 20px;
            font-weight: 600;
            transition: opacity .2s, transform .2s;
        }
        .nav-links .btn-nav:hover { opacity: .88; transform: translateY(-1px); }
        @media (max-width: 540px) {
            .nav-hide-sm { display: none; }
        }

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
            width: 100%; max-width: 860px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,.6);
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            flex: 0 0 42%;
            background: linear-gradient(155deg, #1e3a8a 0%, #1e293b 55%, #0f172a 100%);
            padding: 48px 36px;
            display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
        }
        .panel-left::before {
            content: '';
            position: absolute; top: -80px; right: -80px;
            width: 240px; height: 240px; border-radius: 50%;
            background: radial-gradient(circle, rgba(79,70,229,.35) 0%, transparent 70%);
            pointer-events: none;
        }
        .panel-left::after {
            content: '';
            position: absolute; bottom: -60px; left: -60px;
            width: 200px; height: 200px; border-radius: 50%;
            background: radial-gradient(circle, rgba(6,182,212,.22) 0%, transparent 70%);
            pointer-events: none;
        }

        /* logo row: icon + text side by side */
        .brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            position: relative; z-index: 1;
        }
        .brand-icon {
            width: 54px; height: 54px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #fff;
            box-shadow: 0 8px 24px rgba(79,70,229,.45);
            flex-shrink: 0;
        }
        .brand-name {
            font-size: 20px; font-weight: 800; color: #fff;
            line-height: 1.2;
        }
        .brand-name span { color: #06b6d4; }

        .brand-desc {
            font-size: 13px; color: #94a3b8;
            line-height: 1.65; margin-bottom: 32px;
            position: relative; z-index: 1;
        }
        .left-stats {
            display: flex; gap: 28px;
            position: relative; z-index: 1;
        }
        .left-stat .num {
            font-size: 22px; font-weight: 800; color: #fff;
        }
        .left-stat .lbl {
            font-size: 11px; color: #64748b;
            font-weight: 500; margin-top: 2px;
        }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            background: rgba(255,255,255,.97);
            padding: 48px 44px 40px;
        }
        .welcome-title {
            font-size: 28px; font-weight: 800;
            color: #0f172a; margin-bottom: 4px;
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
            background: #f8fafc; border-color: #e2e8f0; color: #94a3b8;
        }
        .form-control {
            border-color: #e2e8f0; font-size: 14px;
            color: #0f172a; background: #f8fafc;
        }
        /* hide Edge/IE built-in password reveal button */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        /* hide Chrome/Edge Chromium built-in reveal */
        input[type="password"]::-webkit-credentials-auto-fill-button { display: none !important; }
        input::-webkit-password-toggle-button { display: none !important; }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
            background: #fff;
        }

        .btn-signin {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border: none; color: #fff; font-weight: 700;
            font-size: 15px; padding: 13px; border-radius: 12px;
            width: 100%; cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 6px 20px rgba(79,70,229,.35);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-signin:hover   { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(79,70,229,.45); }
        .btn-signin:active  { transform: scale(.98); }
        .btn-signin:disabled{ opacity: .6; cursor: not-allowed; }

        .alert-err {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #991b1b; border-radius: 10px;
            padding: 10px 14px; font-size: 13px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        .foot { text-align: center; margin-top: 20px; }
        .foot a { font-size: 13px; color: #64748b; text-decoration: none; }
        .foot a:hover { color: #4f46e5; }
        .foot .dot { margin: 0 8px; color: #cbd5e1; }

        .role-pills { display: flex; gap: 6px; justify-content: center; margin-top: 10px; }
        .role-pill  { font-size: 11px; font-weight: 600; border-radius: 50px; padding: 3px 10px; }
        .rp-admin   { background: #ede9fe; color: #5b21b6; }
        .rp-teacher { background: #e0f2fe; color: #0369a1; }
        .rp-student { background: #dcfce7; color: #166534; }

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
            <div class="nav-brand-dot">📷</div>
            <span class="nav-brand-name">Face<span>Attend</span></span>
        </a>
        <div class="nav-links">
            <a href="{{ route('landing') }}" class="nav-hide-sm">Home</a>
            <a href="{{ route('landing') }}#features" class="nav-hide-sm">Features</a>
            <a href="{{ route('landing') }}#how" class="nav-hide-sm">How It Works</a>
            <a href="{{ route('landing') }}#roles" class="nav-hide-sm">For Schools</a>
            <a href="{{ route('landing') }}" class="btn-nav d-inline-flex d-sm-none">Home</a>
            <a href="{{ route('login') }}" class="btn-nav d-none d-sm-inline-flex">Sign In</a>
        </div>
    </div>
</nav>

{{-- ══ CARD ══ --}}
<div class="page-wrap">
    <div class="login-card">

        {{-- ── LEFT PANEL ── --}}
        <div class="panel-left">
            <div class="brand-row">
                <div class="brand-icon"><i class="bi bi-camera-fill"></i></div>
                <div class="brand-name">Face<span>Attend</span><br>System</div>
            </div>
            <div class="brand-desc">
                Recognize faces, mark attendance, and notify parents — all in under a second.
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
            <div class="welcome-sub">Sign in to continue to your portal</div>

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
                    <i class="bi bi-box-arrow-in-right"></i> Sign In →
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
