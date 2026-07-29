<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Ended</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body{background:#0a0a1a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .card{background:#0f172a;border:1px solid rgba(248,113,113,.2);border-radius:24px;padding:40px 32px;max-width:380px;width:100%;text-align:center;box-shadow:0 0 40px rgba(248,113,113,.1);}
        .icon-ring{width:80px;height:80px;border-radius:50%;background:rgba(248,113,113,.1);border:3px solid #f87171;display:flex;align-items:center;justify-content:center;font-size:34px;margin:0 auto 20px;}
        h4{color:#f87171;font-weight:800;margin-bottom:6px;}
        .sub{color:#64748b;font-size:14px;}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-ring">🔒</div>
        <h4>Session Ended</h4>
        <p class="sub">
            The QR code for <strong style="color:#fff;">{{ $session->subject }}</strong> is no longer active.
            This session ended at {{ $session->ended_at?->format('h:i A') ?? 'N/A' }}.
        </p>
        <p style="color:#334155;font-size:12px;margin-top:20px;margin-bottom:0;">
            Please ask your teacher if you need your attendance corrected.
        </p>
    </div>
</body>
</html>
