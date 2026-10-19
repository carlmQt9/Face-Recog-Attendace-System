<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — DMCMES Smart Attendance</title>
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
            background: linear-gradient(135deg, #1a5c2a, #0f3318);
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
        .register-card {
            display: flex;
            width: 100%; max-width: 960px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,.6);
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            flex: 0 0 40%;
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

        /* Info badges */
        .info-badge {
            background: rgba(255,255,255,.06);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 12px; color: rgba(255,255,255,.8);
            position: relative; z-index: 1;
            margin-bottom: 14px;
        }
        .info-badge .badge-title {
            font-size: 10px; font-weight: 700;
            color: #f5a800; text-transform: uppercase;
            letter-spacing: .1em; margin-bottom: 8px;
        }
        .info-badge .badge-item {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 6px;
        }
        .info-badge .badge-item:last-child { margin-bottom: 0; }
        .info-badge .badge-icon { color: #4ade80; }
        .info-badge .badge-text { font-size: 12px; }

        /* ── RIGHT PANEL ── */
        .panel-right {
            flex: 1;
            background: rgba(255,255,255,.97);
            padding: 40px 44px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .welcome-title {
            font-size: 26px; font-weight: 800;
            color: #0c2d6b; margin-bottom: 4px;
        }
        .welcome-sub {
            font-size: 13px; color: #64748b;
            margin-bottom: 24px;
        }

        /* Form */
        .form-label {
            font-size: 11px; font-weight: 700;
            letter-spacing: .6px; text-transform: uppercase;
            color: #475569; margin-bottom: 6px;
        }
        .form-control, .form-select {
            border-color: #e2e8f0; font-size: 14px;
            color: #0f172a; background: #f8fafc;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a5c2a;
            box-shadow: 0 0 0 3px rgba(26,92,42,.12);
            background: #fff;
        }
        .password-field {
            position: relative;
        }
        .password-field .form-control {
            padding-right: 42px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }
        .password-toggle:hover {
            background: rgba(12,61,138,.06);
            color: #0c3d8a;
        }
        .password-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(12,61,138,.18);
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input::-webkit-password-toggle-button,
        input::-webkit-credentials-auto-fill-button,
        input::-webkit-textfield-decoration-container {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .password-strength {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
        }
        .strength-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }
        .strength-bar span {
            display: block;
            height: 100%;
            width: 0%;
            background: #ef4444;
            border-radius: inherit;
            transition: all .2s ease;
        }
        .form-text {
            font-size: 11px;
            color: #64748b;
        }

        .btn-register {
            background: linear-gradient(135deg, #1a5c2a, #0f3318);
            border: none; color: #fff; font-weight: 700;
            font-size: 15px; padding: 13px; border-radius: 12px;
            width: 100%; cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 6px 20px rgba(26,92,42,.35);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-register:hover   { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(26,92,42,.45); }
        .btn-register:active  { transform: scale(.98); }
        .btn-register:disabled{ opacity: .6; cursor: not-allowed; }

        .alert-err {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #991b1b; border-radius: 10px;
            padding: 10px 14px; font-size: 13px; margin-bottom: 18px;
        }
        .alert-err ul { margin: 0; padding-left: 20px; }

        .foot { text-align: center; margin-top: 20px; }

        .role-pills { display: flex; gap: 6px; justify-content: center; margin-top: 10px; flex-wrap: wrap; }
        .role-pill  { font-size: 11px; font-weight: 600; border-radius: 50px; padding: 3px 10px; }
        .rp-admin   { background: #e0e7ff; color: #0c3d8a; }
        .rp-teacher { background: #fef3c7; color: #92400e; }
        .rp-student { background: #dcfce7; color: #166534; }

        /* student fields */
        .student-fields {
            display: none;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

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
        @media (max-width: 768px) {
            .register-card { flex-direction: column; max-width: 480px; }
            .panel-left { padding: 32px 28px; }
            .panel-right { padding: 32px 28px; max-height: none; }
        }

        /* ── Mobile hamburger ── */
        .hamburger-nav {
            display: none;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff;
            width: 38px; height: 38px;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            align-items: center; justify-content: center;
        }
        .mobile-menu {
            display: none;
            flex-direction: column;
            background: rgba(9,26,62,.98);
            border-top: 1px solid rgba(245,168,0,.2);
            padding: 8px 0;
        }
        .mobile-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 24px;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .2s;
        }
        .mobile-menu a:hover, .mobile-menu a.active {
            color: #f5a800;
            background: rgba(245,168,0,.08);
            border-left-color: #f5a800;
        }
        .mobile-menu.open { display: flex; }

        @media (max-width: 540px) {
            .nav-hide-sm { display: none !important; }
            .hamburger-nav { display: inline-flex; }
        }
    </style>
</head>
<body>

{{-- ══ NAVBAR ══ --}}
<nav class="top-nav">
    <div class="nav-inner">
        <a href="{{ route('landing') }}" class="nav-brand">
            <img src="{{ asset('dmcmes-logo.png') }}" alt="DMCMES Logo">
            <div class="nav-brand-text">
                <div class="nav-brand-abbr">DMCMES</div>
                <span class="nav-brand-full">Smart Attendance System</span>
            </div>
        </a>
        <div class="nav-links nav-hide-sm">
            <a href="{{ route('landing') }}">Home</a>
            <a href="{{ route('landing') }}#features">Features</a>
            <a href="{{ route('landing') }}#how">How It Works</a>
            <a href="{{ route('login') }}" class="btn-nav">Sign In</a>
        </div>
        {{-- Mobile hamburger --}}
        <button class="hamburger-nav" id="mobileMenuBtn" onclick="toggleMobileMenu()" aria-label="Menu">
            <i class="bi bi-list" id="menuIcon"></i>
        </button>
    </div>
    {{-- Mobile dropdown --}}
    <div class="mobile-menu" id="mobileMenu">
        <a href="{{ route('landing') }}"><i class="bi bi-house-fill"></i> Home</a>
        <a href="{{ route('landing') }}#features"><i class="bi bi-stars"></i> Features</a>
        <a href="{{ route('landing') }}#how"><i class="bi bi-info-circle-fill"></i> How It Works</a>
        <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Sign In</a>
        <a href="{{ route('register') }}" class="active"><i class="bi bi-person-plus-fill"></i> Register</a>
    </div>
</nav>

{{-- ══ CARD ══ --}}
<div class="page-wrap">
    <div class="register-card">

        {{-- ── LEFT PANEL ── --}}
        <div class="panel-left">
            <div class="brand-row">
                <div class="brand-logo-wrap">
                    <img src="{{ asset('dmcmes-logo.png') }}" alt="DMCMES">
                </div>
                <div class="brand-school">
                    <div class="abbr">DMCMES</div>
                    <span class="full">Don Marcelo C. Marty<br>Elementary School</span>
                </div>
            </div>

            <div class="brand-tagline">
                <strong>Create Your Account</strong> — Join the smart attendance system and experience instant face recognition with real-time parent notifications.
            </div>

            <div class="info-badge">
                <div class="badge-title">🎯 Quick Approval</div>
                <div class="badge-item">
                    <i class="bi bi-check-circle-fill badge-icon"></i>
                    <span class="badge-text"><strong>Students:</strong> Instant access</span>
                </div>
                <div class="badge-item">
                    <i class="bi bi-clock-fill" style="color: #f5a800;"></i>
                    <span class="badge-text"><strong>Teachers & Admins:</strong> Approval required</span>
                </div>
            </div>

            <div class="info-badge">
                <div class="badge-title">🔒 Secure & Private</div>
                <div class="badge-item">
                    <i class="bi bi-shield-fill-check badge-icon"></i>
                    <span class="badge-text">Encrypted face data</span>
                </div>
                <div class="badge-item">
                    <i class="bi bi-shield-fill-check badge-icon"></i>
                    <span class="badge-text">Password protection</span>
                </div>
                <div class="badge-item">
                    <i class="bi bi-shield-fill-check badge-icon"></i>
                    <span class="badge-text">Role-based access</span>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL ── --}}
        <div class="panel-right">

            <div class="welcome-title">Create Account</div>
            <div class="welcome-sub">Register for DMCMES Smart Attendance System</div>

            @if($errors->any())
                <div class="alert-err">
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" id="registerForm">
                @csrf

                <div class="row g-3">
                    {{-- Full Name --}}
                    <div class="col-12">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" placeholder="Juan dela Cruz" required autofocus>
                    </div>

                    {{-- Email --}}
                    <div class="col-12">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" placeholder="you@school.edu" required>
                    </div>

                    {{-- Role --}}
                    <div class="col-12">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">— Select Role —</option>
                            <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        <small class="form-text d-block mt-1">
                            <i class="bi bi-info-circle"></i> Students get instant access. Teachers & Admins require approval.
                        </small>
                    </div>

                    {{-- Student-specific fields --}}
                    <div class="col-12 student-fields" id="studentFields">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Student ID <span class="text-danger">*</span></label>
                                <input type="text" name="student_id" id="student_id" class="form-control"
                                       value="{{ old('student_id') }}" placeholder="2024-0001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <input type="text" name="grade_level" id="grade_level" class="form-control"
                                       value="{{ old('grade_level') }}" placeholder="e.g., Grade 7">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Section <span class="text-danger">*</span></label>
                                <input type="text" name="section" id="section" class="form-control"
                                       value="{{ old('section') }}" placeholder="e.g., Abakada">
                            </div>
                        </div>
                    </div>

                    {{-- Teacher-specific fields --}}
                    <div class="col-12 student-fields" id="teacherFields">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                                <input type="text" name="employee_id" id="employee_id" class="form-control"
                                       value="{{ old('employee_id') }}" placeholder="e.g., EMP-2024-001">
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i> Your unique employee identification number
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="password-field">
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="········" required>
                            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="password-strength mt-2" aria-live="polite">
                            <div class="strength-bar"><span id="passwordStrengthBar"></span></div>
                        </div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:5px;line-height:1.5;">
                            Min 8 chars · A–Z · a–z · 0–9 · symbol (!@#$%^&*)
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="password-field">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                   placeholder="········" required>
                            <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div id="passwordMatchMsg" style="font-size:12px;margin-top:6px;display:none;"></div>
                    </div>

                    {{-- Terms and Conditions --}}
                    <div class="col-12 mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms" style="font-size: 12px; color: #475569; text-transform: none; letter-spacing: normal;">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: #0c3d8a; text-decoration: none; font-weight: 600;">Terms and Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: #0c3d8a; text-decoration: none; font-weight: 600;">Privacy Policy</a> <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register mt-4" id="registerBtn">
                    <i class="bi bi-person-plus-fill"></i> Create Account
                </button>
            </form>

            <div class="foot">
                <div style="margin-top: 16px; color: #64748b; font-size: 13px;">
                    Already have an account? <a href="{{ route('login') }}" style="color: #1a5c2a; font-weight: 600; text-decoration: none;">Sign In</a>
                </div>
                <div class="role-pills mt-2">
                    <span class="role-pill rp-student">Student</span>
                    <span class="role-pill rp-teacher">Teacher</span>
                    <span class="role-pill rp-admin">Administrator</span>
                </div>
            </div>

        </div>{{-- /panel-right --}}
    </div>{{-- /register-card --}}
</div>{{-- /page-wrap --}}

<script>
    function evaluatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score += 25;
        if (/[a-z]/.test(password)) score += 25;
        if (/[A-Z]/.test(password)) score += 20;
        if (/\d/.test(password)) score += 15;
        if (/[^A-Za-z\d]/.test(password)) score += 15;

        return score;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            const targetId = button.getAttribute('data-password-toggle');
            const input = document.getElementById(targetId);
            const icon = button.querySelector('i');

            if (!input || !icon) return;

            button.addEventListener('click', function (event) {
                event.preventDefault();
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.className = isPassword ? 'bi bi-eye' : 'bi bi-eye-slash';
                button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });

        const passwordInput = document.getElementById('password');
        const confirmInput  = document.getElementById('password_confirmation');
        const strengthBar   = document.getElementById('passwordStrengthBar');
        const matchMsg      = document.getElementById('passwordMatchMsg');

        // ── Strength bar ──
        if (passwordInput && strengthBar) {
            passwordInput.addEventListener('input', function () {
                const score = evaluatePasswordStrength(passwordInput.value);
                strengthBar.style.width = score + '%';
                if (!passwordInput.value.length) {
                    strengthBar.style.background = '#e2e8f0';
                } else if (score < 50) {
                    strengthBar.style.background = '#ef4444';
                } else if (score < 80) {
                    strengthBar.style.background = '#f59e0b';
                } else {
                    strengthBar.style.background = '#22c55e';
                }
                // Re-check match whenever password changes
                checkMatch();
            });
        }

        // ── Password match ──
        function checkMatch() {
            if (!confirmInput.value.length) {
                matchMsg.style.display = 'none';
                confirmInput.style.borderColor = '#e2e8f0';
                return;
            }
            matchMsg.style.display = 'block';
            if (passwordInput.value === confirmInput.value) {
                matchMsg.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#22c55e;"></i> <span style="color:#15803d;">Passwords match</span>';
                confirmInput.style.borderColor = '#22c55e';
            } else {
                matchMsg.innerHTML = '<i class="bi bi-x-circle-fill" style="color:#ef4444;"></i> <span style="color:#dc2626;">Passwords do not match</span>';
                confirmInput.style.borderColor = '#ef4444';
            }
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', checkMatch);
        }

        // ── Block submit if passwords don't match ──
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    checkMatch();
                    confirmInput.focus();
                }
            });
        }
    });
</script>

<div class="school-footer">
    © {{ date('Y') }} <span>DMCMES</span> — Don Marcelo C. Marty Elementary School &nbsp;·&nbsp; Smart Attendance System
</div>

{{-- Terms and Conditions Modal --}}
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0c3d8a, #1a6b3c); border: none;">
                <h5 class="modal-title text-white fw-bold" id="termsModalLabel">
                    <i class="bi bi-file-text-fill me-2"></i>Terms and Conditions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                <p class="text-muted small">Last Updated: {{ date('F d, Y') }}</p>

                <h6 class="fw-bold mt-4 mb-2">1. ACCEPTANCE OF TERMS</h6>
                <p style="font-size: 13px; line-height: 1.7;">By registering and using the Don Marcelo C. Marty Elementary School (DMCMES) Face Recognition Attendance System ("the System"), you agree to comply with and be bound by these Terms and Conditions. This System is operated in accordance with:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Republic Act No. 10173</strong> - Data Privacy Act of 2012 (Philippines)</li>
                    <li><strong>Republic Act No. 10627</strong> - Anti-Bullying Act of 2013</li>
                    <li><strong>DepEd Order No. 40, s. 2012</strong> - DepEd Child Protection Policy</li>
                    <li><strong>DepEd Order No. 32, s. 2020</strong> - Interim Guidelines for Assessment and Grading</li>
                    <li><strong>National Privacy Commission Circular No. 16-03</strong> - Security of Personal Data</li>
                    <li><strong>DICT Cybersecurity Framework</strong> - Department of Information and Communications Technology</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">2. DATA COLLECTION AND USAGE</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>2.1 Biometric Data Collection</strong></p>
                <p style="font-size: 13px; line-height: 1.7;">The System collects facial recognition data (biometric information) for the sole purpose of attendance tracking. This collection is compliant with:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>NPC Advisory Opinion No. 2019-032</strong> - Processing of Biometric Data</li>
                    <li><strong>ISO/IEC 27001:2013</strong> - Information Security Management</li>
                    <li><strong>ISO/IEC 27701:2019</strong> - Privacy Information Management</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>2.2 Information We Collect:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Full name, email address, student/employee ID</li>
                    <li>Facial biometric templates (encrypted mathematical representations)</li>
                    <li>Attendance timestamps (date, time-in, time-out)</li>
                    <li>Camera location and attendance method</li>
                    <li>IP addresses and session logs for security purposes</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>2.3 Purpose and Lawful Basis:</strong></p>
                <p style="font-size: 13px; line-height: 1.7;">Data processing is based on legitimate educational interest under <strong>Section 12 of the Data Privacy Act</strong> and explicit consent. The purposes include:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Accurate and automated attendance recording</li>
                    <li>Student safety and welfare monitoring</li>
                    <li>Parent/guardian notification of attendance status</li>
                    <li>Academic record-keeping and reporting to DepEd</li>
                    <li>Compliance with DepEd attendance requirements</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">3. DATA PRIVACY AND SECURITY</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>3.1 Security Measures</strong> (NPC Circular 16-03 Compliant):</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Encryption:</strong> AES-256 encryption for biometric templates at rest and in transit</li>
                    <li><strong>Access Control:</strong> Role-based access with multi-factor authentication</li>
                    <li><strong>Audit Trails:</strong> Comprehensive logging of all data access and modifications</li>
                    <li><strong>Data Minimization:</strong> Only essential data is collected and retained</li>
                    <li><strong>Secure Storage:</strong> Encrypted database servers with regular security audits</li>
                    <li><strong>Network Security:</strong> Firewalls, intrusion detection systems, and secure HTTPS connections</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>3.2 Data Retention:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Attendance records: Retained for <strong>5 years</strong> as per DepEd records management guidelines</li>
                    <li>Biometric templates: Retained while user is active in the system</li>
                    <li>Deleted within <strong>30 days</strong> upon graduation, transfer, or account deactivation request</li>
                    <li>Anonymous statistical data may be retained for system improvement</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">4. USER RIGHTS (Data Privacy Act Section 16-19)</h6>
                <p style="font-size: 13px; line-height: 1.7;">You have the following rights regarding your personal data:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Right to be Informed:</strong> You will be notified of data collection and processing activities</li>
                    <li><strong>Right to Access:</strong> Request copies of your personal data held by the System</li>
                    <li><strong>Right to Rectification:</strong> Correct inaccurate or incomplete personal information</li>
                    <li><strong>Right to Erasure:</strong> Request deletion of your data (subject to legal retention requirements)</li>
                    <li><strong>Right to Data Portability:</strong> Receive your data in a structured, machine-readable format</li>
                    <li><strong>Right to Object:</strong> Object to data processing under certain circumstances</li>
                    <li><strong>Right to Damages:</strong> Claim compensation for damages sustained due to data breaches</li>
                </ul>
                <p style="font-size: 13px; line-height: 1.7;">To exercise these rights, contact the School Data Protection Officer at the school's administrative office.</p>

                <h6 class="fw-bold mt-4 mb-2">5. PARENTAL CONSENT (For Minors)</h6>
                <p style="font-size: 13px; line-height: 1.7;">In compliance with <strong>DepEd Order No. 40, s. 2012 (Child Protection Policy)</strong> and <strong>Republic Act No. 7610 (Special Protection of Children Against Abuse)</strong>:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Students under 18 years old require parental/guardian consent for biometric data processing</li>
                    <li>Parents/guardians will be notified of attendance via email in real-time</li>
                    <li>Parents have the right to request deletion of their child's biometric data</li>
                    <li>The school will obtain written consent from parents during enrollment</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">6. ACCEPTABLE USE POLICY</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>6.1 Prohibited Activities:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Attempting to bypass face recognition using photos, videos, or masks</li>
                    <li>Sharing login credentials with others</li>
                    <li>Unauthorized access to other users' accounts or data</li>
                    <li>Tampering with cameras or system infrastructure</li>
                    <li>Using the System for purposes other than attendance tracking</li>
                    <li>Marking attendance on behalf of others (proxy attendance)</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>6.2 Consequences of Violation:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Account suspension or termination</li>
                    <li>Disciplinary action in accordance with school policies</li>
                    <li>Legal action for serious violations (e.g., data theft, system sabotage)</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">7. DATA BREACH NOTIFICATION</h6>
                <p style="font-size: 13px; line-height: 1.7;">In compliance with <strong>NPC Circular No. 16-05 (Data Breach Notification)</strong>:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>The school will notify the National Privacy Commission within <strong>72 hours</strong> of discovering a data breach</li>
                    <li>Affected users will be notified within <strong>5 days</strong> via email and school announcement</li>
                    <li>Notification will include: nature of breach, data affected, mitigation measures, and contact information</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">8. THIRD-PARTY SERVICES</h6>
                <p style="font-size: 13px; line-height: 1.7;">The System may integrate with third-party services for email notifications (Gmail API) and cloud storage. These services are:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>GDPR and ISO 27001 certified</li>
                    <li>Subject to their own terms and privacy policies</li>
                    <li>Used only for system functionality, not for marketing or profiling</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">9. SYSTEM AVAILABILITY AND LIMITATIONS</h6>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>The System is provided "as is" without warranty of uninterrupted operation</li>
                    <li>Scheduled maintenance will be announced in advance</li>
                    <li>Manual attendance fallback is available during system downtime</li>
                    <li>Face recognition accuracy may be affected by lighting, camera quality, and facial obstructions</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">10. LIABILITY AND INDEMNIFICATION</h6>
                <p style="font-size: 13px; line-height: 1.7;">The school shall not be liable for:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Indirect, incidental, or consequential damages arising from System use</li>
                    <li>Data loss due to user negligence (e.g., sharing passwords)</li>
                    <li>Attendance discrepancies due to user error or system malfunction</li>
                </ul>
                <p style="font-size: 13px; line-height: 1.7;">Users agree to indemnify the school against claims arising from violation of these Terms.</p>

                <h6 class="fw-bold mt-4 mb-2">11. AMENDMENTS TO TERMS</h6>
                <p style="font-size: 13px; line-height: 1.7;">The school reserves the right to modify these Terms at any time. Users will be notified of significant changes via:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Email notification</li>
                    <li>In-system announcement</li>
                    <li>Updated "Last Modified" date on this document</li>
                </ul>
                <p style="font-size: 13px; line-height: 1.7;">Continued use of the System after changes constitutes acceptance of updated Terms.</p>

                <h6 class="fw-bold mt-4 mb-2">12. GOVERNING LAW AND JURISDICTION</h6>
                <p style="font-size: 13px; line-height: 1.7;">These Terms are governed by the laws of the Republic of the Philippines. Any disputes shall be resolved through:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Internal school grievance mechanisms</li>
                    <li>DepEd Central Office or Division Office mediation</li>
                    <li>National Privacy Commission for data privacy complaints</li>
                    <li>Philippine courts with jurisdiction in Zambales</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">13. CONTACT INFORMATION</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>Data Protection Officer:</strong></p>
                <p style="font-size: 13px; line-height: 1.7;">
                    Don Marcelo C. Marty Elementary School<br>
                    Sta. Cruz, Zambales<br>
                    Email: dmcmes.dpo@deped.gov.ph<br>
                    Phone: [School Contact Number]
                </p>

                <p style="font-size: 13px; line-height: 1.7;"><strong>National Privacy Commission:</strong></p>
                <p style="font-size: 13px; line-height: 1.7;">
                    5th Floor, Philippine International Convention Center (PICC)<br>
                    Vicente Sotto St., Pasay City, Metro Manila 1307<br>
                    Email: info@privacy.gov.ph<br>
                    Hotline: (02) 8234-2228
                </p>

                <h6 class="fw-bold mt-4 mb-2">14. COMPLIANCE CERTIFICATIONS</h6>
                <p style="font-size: 13px; line-height: 1.7;">This System is designed to comply with:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>ISO/IEC 27001:2013</strong> - Information Security Management System</li>
                    <li><strong>ISO/IEC 27701:2019</strong> - Privacy Information Management System</li>
                    <li><strong>ISO/IEC 30107-3:2017</strong> - Biometric Presentation Attack Detection</li>
                    <li><strong>NIST SP 800-53</strong> - Security and Privacy Controls</li>
                    <li><strong>DepEd ICT Standards</strong> - Department of Education Technology Guidelines</li>
                </ul>

                <hr class="my-4">
                <p style="font-size: 12px; color: #64748b; font-style: italic;">
                    By clicking "I agree" and creating an account, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Privacy Policy Modal --}}
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0c3d8a, #1a6b3c); border: none;">
                <h5 class="modal-title text-white fw-bold" id="privacyModalLabel">
                    <i class="bi bi-shield-lock-fill me-2"></i>Privacy Policy
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                <p class="text-muted small">Last Updated: {{ date('F d, Y') }}</p>

                <h6 class="fw-bold mt-4 mb-2">INTRODUCTION</h6>
                <p style="font-size: 13px; line-height: 1.7;">Don Marcelo C. Marty Elementary School ("we," "our," or "the School") is committed to protecting your privacy and personal data. This Privacy Policy explains how we collect, use, disclose, and safeguard your information in compliance with Philippine data privacy laws.</p>

                <h6 class="fw-bold mt-4 mb-2">LEGAL BASIS</h6>
                <p style="font-size: 13px; line-height: 1.7;">This Privacy Policy is issued pursuant to:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Republic Act No. 10173</strong> - Data Privacy Act of 2012</li>
                    <li><strong>IRR of the Data Privacy Act</strong> (NPC Circular No. 16-01)</li>
                    <li><strong>NPC Advisory Opinion No. 2019-032</strong> - Biometric Data Processing</li>
                    <li><strong>NPC Circular No. 16-03</strong> - Security of Personal Data</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">WHAT INFORMATION WE COLLECT</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>Personal Information:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Name, email address, student/employee ID</li>
                    <li>Role (student, teacher, administrator)</li>
                    <li>School year, grade level, section (for students)</li>
                    <li>Department, employee ID (for teachers)</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>Sensitive Personal Information (Biometric Data):</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Facial recognition templates (encrypted mathematical representations)</li>
                    <li>Attendance camera snapshots (stored securely, not publicly accessible)</li>
                </ul>

                <p style="font-size: 13px; line-height: 1.7;"><strong>Usage Information:</strong></p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Attendance records (date, time-in, time-out, location)</li>
                    <li>Login timestamps and IP addresses</li>
                    <li>System access logs for security purposes</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">HOW WE USE YOUR INFORMATION</h6>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Attendance Tracking:</strong> Automated marking and record-keeping</li>
                    <li><strong>Parent Notification:</strong> Real-time email alerts to parents/guardians</li>
                    <li><strong>Academic Records:</strong> Integration with school records and DepEd reporting</li>
                    <li><strong>Security:</strong> Prevention of attendance fraud and unauthorized access</li>
                    <li><strong>System Improvement:</strong> Anonymous analytics for performance optimization</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">DATA SHARING AND DISCLOSURE</h6>
                <p style="font-size: 13px; line-height: 1.7;">We do <strong>NOT</strong> sell, rent, or trade your personal data. We may share data only in the following circumstances:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Parents/Guardians:</strong> Attendance notifications for minor students</li>
                    <li><strong>DepEd Officials:</strong> Compliance with educational reporting requirements</li>
                    <li><strong>Service Providers:</strong> Cloud hosting, email services (under strict data processing agreements)</li>
                    <li><strong>Legal Obligations:</strong> When required by law, court order, or government authority</li>
                    <li><strong>Emergency Situations:</strong> To protect student safety or prevent harm</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">YOUR PRIVACY RIGHTS</h6>
                <p style="font-size: 13px; line-height: 1.7;">Under the Data Privacy Act, you have the right to:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Update inaccurate or incomplete information</li>
                    <li><strong>Erasure:</strong> Request deletion (subject to legal retention)</li>
                    <li><strong>Object:</strong> Oppose processing for legitimate reasons</li>
                    <li><strong>Data Portability:</strong> Receive your data in a machine-readable format</li>
                    <li><strong>Withdraw Consent:</strong> Revoke consent at any time (may affect system access)</li>
                </ul>
                <p style="font-size: 13px; line-height: 1.7;">To exercise your rights, contact the School Data Protection Officer.</p>

                <h6 class="fw-bold mt-4 mb-2">DATA SECURITY MEASURES</h6>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li><strong>Encryption:</strong> AES-256 for data at rest, TLS 1.3 for data in transit</li>
                    <li><strong>Access Control:</strong> Role-based permissions, multi-factor authentication</li>
                    <li><strong>Audit Logging:</strong> All data access and changes are logged</li>
                    <li><strong>Regular Audits:</strong> Quarterly security assessments and penetration testing</li>
                    <li><strong>Employee Training:</strong> Mandatory data privacy training for all staff</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">DATA RETENTION AND DELETION</h6>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Attendance records: <strong>5 years</strong> (DepEd requirement)</li>
                    <li>Biometric templates: Deleted within <strong>30 days</strong> of account closure</li>
                    <li>Anonymous analytics: Retained indefinitely for system improvement</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">CHILDREN'S PRIVACY</h6>
                <p style="font-size: 13px; line-height: 1.7;">Students under 18 require parental consent for biometric data processing. Parents can:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Access their child's data at any time</li>
                    <li>Request deletion of biometric data</li>
                    <li>Opt for manual attendance as an alternative</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">COOKIES AND TRACKING</h6>
                <p style="font-size: 13px; line-height: 1.7;">We use essential cookies for:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Session management (keeping you logged in)</li>
                    <li>Security (preventing unauthorized access)</li>
                </ul>
                <p style="font-size: 13px; line-height: 1.7;">We do <strong>NOT</strong> use advertising or tracking cookies.</p>

                <h6 class="fw-bold mt-4 mb-2">INTERNATIONAL DATA TRANSFERS</h6>
                <p style="font-size: 13px; line-height: 1.7;">Your data is stored in secure servers within the Philippines. If data is transferred internationally (e.g., for cloud backup), we ensure:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Adequate data protection measures (NPC-approved)</li>
                    <li>Data processing agreements with service providers</li>
                    <li>Compliance with GDPR and international privacy standards</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">UPDATES TO THIS POLICY</h6>
                <p style="font-size: 13px; line-height: 1.7;">We may update this Privacy Policy periodically. You will be notified of material changes via:</p>
                <ul style="font-size: 13px; line-height: 1.7;">
                    <li>Email notification</li>
                    <li>In-system announcement</li>
                    <li>Updated "Last Modified" date</li>
                </ul>

                <h6 class="fw-bold mt-4 mb-2">CONTACT US</h6>
                <p style="font-size: 13px; line-height: 1.7;"><strong>Data Protection Officer:</strong><br>
                Don Marcelo C. Marty Elementary School<br>
                Sta. Cruz, Zambales<br>
                Email: dmcmes.dpo@deped.gov.ph</p>

                <p style="font-size: 13px; line-height: 1.7;"><strong>File a Complaint:</strong><br>
                National Privacy Commission<br>
                Email: complaints@privacy.gov.ph<br>
                Website: privacy.gov.ph</p>

                <hr class="my-4">
                <p style="font-size: 12px; color: #64748b; font-style: italic;">
                    By using this System, you acknowledge that you have read and understood this Privacy Policy.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const icon = document.getElementById('menuIcon');
    menu.classList.toggle('open');
    icon.className = menu.classList.contains('open') ? 'bi bi-x-lg' : 'bi bi-list';
}
document.querySelectorAll('.mobile-menu a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.remove('open');
        document.getElementById('menuIcon').className = 'bi bi-list';
    });
});
</script>
<script>
// Show/hide role-specific fields based on selection
document.getElementById('role').addEventListener('change', function() {
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');
    const studentIdInput = document.getElementById('student_id');
    const gradeLevelInput = document.getElementById('grade_level');
    const sectionInput = document.getElementById('section');
    const employeeIdInput = document.getElementById('employee_id');
    
    if (this.value === 'student') {
        studentFields.style.display = 'block';
        teacherFields.style.display = 'none';
        studentIdInput.required = true;
        gradeLevelInput.required = true;
        sectionInput.required = true;
        employeeIdInput.required = false;
    } else if (this.value === 'teacher') {
        studentFields.style.display = 'none';
        teacherFields.style.display = 'block';
        studentIdInput.required = false;
        gradeLevelInput.required = false;
        sectionInput.required = false;
        employeeIdInput.required = true;
    } else {
        studentFields.style.display = 'none';
        teacherFields.style.display = 'none';
        studentIdInput.required = false;
        gradeLevelInput.required = false;
        sectionInput.required = false;
        employeeIdInput.required = false;
    }
});

// Trigger on page load for old input
window.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
    }
});

// Form submission animation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const termsCheckbox = document.getElementById('terms');
    if (!termsCheckbox.checked) {
        e.preventDefault();
        alert('You must agree to the Terms and Conditions and Privacy Policy to register.');
        return false;
    }
    
    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account…';
});
</script>
</body>
</html>
