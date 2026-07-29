<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Marked</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body{background:#0a0a1a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .card{background:#0f172a;border:1px solid rgba(250,204,21,.2);border-radius:24px;padding:40px 32px;max-width:380px;width:100%;text-align:center;box-shadow:0 0 40px rgba(250,204,21,.08);}
        .icon-ring{width:80px;height:80px;border-radius:50%;background:rgba(250,204,21,.08);border:3px solid #facc15;display:flex;align-items:center;justify-content:center;font-size:34px;margin:0 auto 20px;}
        h4{color:#facc15;font-weight:800;margin-bottom:6px;}
        .sub{color:#64748b;font-size:14px;}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-ring">⚠️</div>
        <h4>Already Marked</h4>
        <p class="sub">
            <strong style="color:#fff;">{{ $student->user->name }}</strong> is already marked present
            for <strong style="color:#fff;">{{ $session->subject }}</strong>.
        </p>
        <p style="color:#334155;font-size:12px;margin-top:20px;margin-bottom:0;">
            You can only mark attendance once per session.
        </p>
    </div>
</body>
</html>
