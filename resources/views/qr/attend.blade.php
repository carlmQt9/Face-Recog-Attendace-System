<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance — {{ $session->subject }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#0a0a1a; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .qr-card {
            background:#0f172a; border:1px solid rgba(255,255,255,.08);
            border-radius:24px; padding:32px 28px; max-width:420px; width:100%;
        }
        .session-badge {
            display:inline-flex; align-items:center; gap:7px;
            background:rgba(12,61,138,.15); border:1px solid rgba(12,61,138,.3);
            border-radius:50px; padding:5px 14px; font-size:13px;
            color:#4a7fd4; font-weight:700; margin-bottom:20px;
        }
        .qr-card h4 { color:#fff; font-weight:800; margin-bottom:4px; }
        .qr-card .sub { color:#475569; font-size:14px; margin-bottom:24px; }
        .form-select {
            background:#1e293b !important; border-color:rgba(255,255,255,.12) !important;
            color:#fff !important; border-radius:12px; padding:12px 16px;
            font-size:15px;
        }
        .form-select option { background:#1e293b; color:#fff; }
        .btn-mark {
            background:linear-gradient(135deg,#0c3d8a,#1a6b3c);
            color:#fff; border:none; border-radius:14px; padding:14px;
            font-size:15px; font-weight:700; width:100%; margin-top:14px;
            cursor:pointer; transition:opacity .2s;
        }
        .btn-mark:hover { opacity:.88; }
        .alert-err { background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.25); color:#f87171; border-radius:12px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="text-center">
            <div class="session-badge">
                <i class="bi bi-qr-code-scan"></i> QR Attendance
            </div>
            <h4>{{ $session->subject }}</h4>
            <p class="sub">{{ $session->section }} &nbsp;·&nbsp; Started {{ $session->started_at?->format('h:i A') }}</p>
        </div>

        @if(session('error'))
            <div class="alert-err"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
        @endif

        <form action="{{ route('qr.attend.mark', [$session->id, $token]) }}" method="POST">
            @csrf
            <label style="color:#94a3b8;font-size:13px;font-weight:600;margin-bottom:8px;display:block;">
                Select your name
            </label>
            <select name="student_id" class="form-select" required>
                <option value="">— Choose your name —</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                @endforeach
            </select>
            @error('student_id')
                <div style="color:#f87171;font-size:12px;margin-top:6px;">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn-mark">
                <i class="bi bi-check2-circle me-2"></i>Mark Me Present
            </button>
        </form>

        <p style="color:#334155;font-size:12px;text-align:center;margin-top:16px;margin-bottom:0;">
            <i class="bi bi-shield-check me-1"></i>You can only mark attendance once per session
        </p>
    </div>
</body>
</html>
