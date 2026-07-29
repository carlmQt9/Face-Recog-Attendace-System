<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Time-Out Alert</title>
<style>
  body{font-family:Arial,sans-serif;background:#f0f4f8;margin:0;padding:24px;}
  .wrap{max-width:520px;margin:auto;}
  .card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);}
  .top{background:linear-gradient(135deg,#2563eb,#7c3aed);padding:28px 28px 20px;text-align:center;}
  .top .icon{font-size:40px;margin-bottom:8px;}
  .top h2{color:#fff;margin:0;font-size:20px;font-weight:800;}
  .top p{color:rgba(255,255,255,.8);margin:4px 0 0;font-size:13px;}
  .body{padding:28px;}
  .greeting{font-size:15px;color:#374151;margin-bottom:20px;}
  .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-bottom:20px;}
  .row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #dbeafe;}
  .row:last-child{border-bottom:none;}
  .lbl{font-size:12px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.05em;min-width:90px;}
  .val{font-size:14px;color:#111827;font-weight:600;}
  .badge-out{display:inline-block;background:#dbeafe;color:#1d4ed8;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:700;}
  .duration-box{background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;padding:14px 20px;text-align:center;margin-bottom:16px;}
  .duration-box .dur-label{font-size:12px;color:#7c3aed;font-weight:700;text-transform:uppercase;letter-spacing:.05em;}
  .duration-box .dur-value{font-size:28px;font-weight:800;color:#6d28d9;margin-top:4px;}
  .note{font-size:13px;color:#6b7280;line-height:1.6;margin-top:16px;}
  .footer{background:#f9fafb;padding:14px 28px;text-align:center;font-size:11px;color:#9ca3af;border-top:1px solid #f3f4f6;}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="top">
    <div class="icon">🏠</div>
    <h2>Student Has Left School</h2>
    <p>Time-Out Notification</p>
  </div>
  <div class="body">
    <p class="greeting">
      Dear <strong>{{ $record->student->parent->parent_name ?? 'Parent/Guardian' }}</strong>,
    </p>
    <div class="info-box">
      <div class="row"><span class="lbl">Student</span><span class="val">{{ $record->student->user->name }}</span></div>
      <div class="row"><span class="lbl">Date</span><span class="val">{{ $record->time_out->format('F j, Y') }}</span></div>
      <div class="row"><span class="lbl">Time In</span><span class="val">{{ $record->arrived_at->format('h:i A') }}</span></div>
      <div class="row"><span class="lbl">Time Out</span><span class="val">{{ $record->time_out->format('h:i A') }}</span></div>
      <div class="row"><span class="lbl">Location</span><span class="val">{{ $record->camera->location }}</span></div>
      @if($record->classSession)
      <div class="row"><span class="lbl">Subject</span><span class="val">{{ $record->classSession->subject }}</span></div>
      @endif
      <div class="row"><span class="lbl">Status</span><span class="val"><span class="badge-out">🏠 Departed</span></span></div>
    </div>
    @if($record->durationMinutes() !== null)
    <div class="duration-box">
      <div class="dur-label">Total Time at School</div>
      <div class="dur-value">{{ $record->durationLabel() }}</div>
    </div>
    @endif
    <p class="note">
      <strong>{{ $record->student->user->name }}</strong> has been recorded as having
      <strong>left school</strong> at <strong>{{ $record->time_out->format('h:i A') }}</strong>.
      Please ensure they arrive home safely.
    </p>
  </div>
  <div class="footer">Automated message from the School Face Recognition Attendance System. Do not reply.</div>
</div>
</div>
</body>
</html>
