<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Alert</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 30px; max-width: 540px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1a73e8; color: #fff; padding: 18px 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .header h2 { margin: 0; font-size: 20px; }
        .body { padding: 24px; }
        .info-row { margin: 10px 0; font-size: 15px; color: #333; }
        .info-row strong { color: #1a73e8; }
        .footer { margin-top: 24px; font-size: 12px; color: #999; text-align: center; }
        .badge { display: inline-block; background: #e6f4ea; color: #137333; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>📋 Attendance Alert</h2>
        </div>
        <div class="body">
            <p>Hi <strong>{{ $record->student->parent->parent_name ?? 'Parent/Guardian' }}</strong>,</p>

            <p>This is an automated update from the school attendance system.</p>

            <div class="info-row">
                <strong>Student:</strong> {{ $record->student->user->name }}
            </div>
            <div class="info-row">
                <strong>Location:</strong> {{ $record->camera->location }}
            </div>
            <div class="info-row">
                <strong>Time:</strong> {{ $record->arrived_at->format('h:i A') }}
            </div>
            <div class="info-row">
                <strong>Date:</strong> {{ $record->arrived_at->format('F j, Y') }}
            </div>
            <div class="info-row">
                <strong>Status:</strong> <span class="badge">✅ Present</span>
            </div>

            <p style="margin-top: 20px;">
                <strong>{{ $record->student->user->name }}</strong> was successfully recognized
                at the <strong>{{ $record->camera->location }}</strong> camera and marked present today
                at <strong>{{ $record->arrived_at->format('h:i A') }}</strong>.
            </p>

            <p>Have a great day!</p>
        </div>
        <div class="footer">
            This is an automated message. Please do not reply to this email.<br>
            School Face Recognition Attendance System
        </div>
    </div>
</body>
</html>
