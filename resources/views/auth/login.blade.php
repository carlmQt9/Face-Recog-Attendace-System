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
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            position: relative; overflow: hidden;
        }
        /* Subtle gradient orbs */
        body::before {
            content: '';
            position: fixed; top: -200px; right: -200px;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(79,70,229,.18) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed; bottom: -200px; left: -200px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(6,182,212,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .card {
            background: rgba(255,255,255,.97);
            border-radius: 24px;
            padding: 44px 40px 36px;
            width: 100%; max-width: 420px;
            box-shadow: 0 32px 80px rgba(0,0,0,.5);
            position: relative; z-index: 1;
        }

        /* Brand */
        .brand-wrap { text-align: center; margin-bottom: 32px; }
        .brand-icon {
            width: 68px; height: 68px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #fff; margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(79,70,229,.4);
        }
        .brand-title { font-size: 22px; font-weight: 800; color: #0f172a; }
        .brand-sub   { font-size: 13px; color: #94a3b8; margin-top: 4px; }

        /* Form elements */
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .input-group-text {
            background: #f8fafc; border-color: #e2e8f0; color: #94a3b8;
        }
        .form-control {
            border-color: #e2e8f0; font-size: 14px; color: #0f172a;
            background: #f8fafc;
        }
        .form-control:focus {
            border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12);
            background: #fff;
        }

        /* Sign in button */
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

        /* Error */
        .alert-err {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #991b1b; border-radius: 10px;
            padding: 10px 14px; font-size: 13px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        /* Footer links */
        .foot { text-align: center; margin-top: 24px; }
        .foot a { font-size: 13px; color: #64748b; text-decoration: none; }
        .foot a:hover { color: #4f46e5; }
        .foot .dot { margin: 0 8px; color: #cbd5e1; }

        /* Role badges */
        .role-pills { display: flex; gap: 6px; justify-content: center; margin-top: 10px; }
        .role-pill {
            font-size: 11px; font-weight: 600; border-radius: 50px;
            padding: 3px 10px;
        }
        .rp-admin   { background: #ede9fe; color: #5b21b6; }
        .rp-teacher { background: #e0f2fe; color: #0369a1; }
        .rp-student { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

{{-- If already authenticated, redirect immediately --}}
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

<div class="card">

    {{-- Brand --}}
    <div class="brand-wrap">
        <div class="brand-icon"><i class="bi bi-camera-fill"></i></div>
        <div class="brand-title">Face Attendance</div>
        <div class="brand-sub">Sign in to your account</div>
    </div>

    {{-- Error --}}
    @if($errors->any())
        <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form --}}
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
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember"
                       style="font-size:13px;color:#64748b;">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn-signin" id="signInBtn">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </form>

    {{-- Footer --}}
    <div class="foot">
        <a href="{{ route('landing') }}">← Back to Home</a>
        <span class="dot">·</span>
        <div class="role-pills mt-2">
            <span class="role-pill rp-admin">Admin</span>
            <span class="role-pill rp-teacher">Teacher</span>
            <span class="role-pill rp-student">Student</span>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye-slash';
    } else {
        f.type = 'password';
        i.className = 'bi bi-eye';
    }
}

// Disable button on submit to prevent double-click
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('signInBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
});
</script>
</body>
</html>
