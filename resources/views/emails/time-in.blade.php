<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Time-In Alert</title>
<style>
  body{font-family:Arial,sans-serif;background:#f0f4f8;margin:0;padding:24px;}
  .wrap{max-width:520px;margin:auto;}
  .card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);}
  .top{background:linear-gradient(135deg,#16a34a,#22c55e);padding:28px 28px 20px;text-align:center;}
  .top .icon{font-size:40px;margin-bottom:8px;}
  .top h2{color:#fff;margin:0;font-size:20px;font-weight:800;}
  .top p{color:rgba(255,255,255,.8);margin:4px 0 0;font-size:13px;}
  .body{padding:28px;}
  .greeting{font-size:15px;color:#374151;margin-bottom:20px;}
  .info-box{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin-bottom:20px;}
  .row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #dcfce7;}
  .row:last-child{border-bottom:none;}
  .lbl{font-size:12px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.05em;min-width:90px;}
  .val{font-size:14px;color:#111827;font-weight:600;}
  .badge{display:inline-block;background:#dcfce7;color:#15803d;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:700;}
  .note{font-size:13px;color:#6b7280;line-height:1.6;margin-top:16px;}
  .footer{background:#f9fafb;padding:14px 28px;text-align:center;font-size:11px;color:#9ca3af;border-top:1px solid #f3f4f6;}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="top">
    <div class="icon">🏫</div>
    <h2>Student Arrived at School</h2>
    <p>Time-In Notification</p>
  </div>
  <div class="body">
    <p class="greeting">
      Dear <strong>{{ $record->student->parent->parent_name ?? 'Parent/Guardian' }}</strong>,
    </p>
    <div class="info-box">
      <div class="row"><span class="lbl">Student</span><span class="val">{{ $record->student->user->name }}</span></div>
      <div class="row"><span class="lbl">Date</span><span class="val">{{ $record->arrived_at->format('F j, Y') }}</span></div>
      <div class="row"><span class="lbl">Time In</span><span class="val">{{ $record->arrived_at->format('h:i A') }}</span></div>
      <div class="row"><span class="lbl">Location</span><span class="val">{{ $record->camera->location }}</span></div>
      @if($record->classSession)
      <div class="row"><span class="lbl">Subject</span><span class="val">{{ $record->classSession->subject }} — {{ $record->classSession->section }}</span></div>
      @endif
      <div class="row"><span class="lbl">Method</span><span class="val">{{ ucfirst(str_replace('_',' ',$record->method)) }}</span></div>
      <div class="row"><span class="lbl">Status</span><span class="val"><span class="badge">✅ Present</span></span></div>
    </div>
    <p class="note">
      <strong>{{ $record->student->user->name }}</strong> has been successfully recorded as
      <strong>present</strong> at <strong>{{ $record->arrived_at->format('h:i A') }}</strong> today.
      You will receive another notification when they leave.
    </p>
  </div>
  <div class="footer">Automated message from the School Face Recognition Attendance System. Do not reply.</div>
</div>
</div>
</body>
</html>
