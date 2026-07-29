<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Marked</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#0a0a1a; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card {
            background:#0f172a; border:1px solid rgba(74,222,128,.2);
            border-radius:24px; padding:40px 32px; max-width:380px; width:100%; text-align:center;
            box-shadow: 0 0 60px rgba(74,222,128,.15);
        }
        .check-ring {
            width:80px; height:80px; border-radius:50%;
            background:rgba(74,222,128,.12); border:3px solid #4ade80;
            display:flex; align-items:center; justify-content:center;
            font-size:36px; margin:0 auto 20px;
            animation: popIn .4s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn{from{opacity:0;transform:scale(.5)}to{opacity:1;transform:scale(1)}}
        h4 { color:#4ade80; font-weight:800; margin-bottom:6px; }
        .sub { color:#64748b; font-size:14px; }
        .info-row { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:12px; padding:12px 16px; margin-top:20px; text-align:left; }
        .info-row .lbl { font-size:11px; color:#475569; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
        .info-row .val { font-size:14px; color:#fff; font-weight:600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check-ring">✅</div>
        <h4>Attendance Marked!</h4>
        <p class="sub">You have been marked present for this session.</p>

        <div class="info-row">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="lbl">Student</div>
                    <div class="val">{{ $student->user->name }}</div>
                </div>
                <div class="text-end">
                    <div class="lbl">Time</div>
                    <div class="val">{{ $record->arrived_at->format('h:i A') }}</div>
                </div>
            </div>
            <div>
                <div class="lbl">Session</div>
                <div class="val">{{ $session->subject }} — {{ $session->section }}</div>
            </div>
        </div>

        <p style="color:#334155;font-size:12px;margin-top:20px;margin-bottom:0;">
            <i class="bi bi-envelope-check me-1"></i>
            {{ $student->parent ? 'Your parent has been notified.' : 'You may now close this page.' }}
        </p>
    </div>
</body>
</html>
