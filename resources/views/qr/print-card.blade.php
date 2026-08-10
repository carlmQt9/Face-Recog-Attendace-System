<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Card — {{ $student->user->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
            display: flex; flex-direction: column;
            align-items: center; justify-content: flex-start;
            min-height: 100vh; padding: 32px 20px;
        }

        /* ── Print action bar (hidden on print) ── */
        .action-bar {
            display: flex; gap: 12px; margin-bottom: 28px;
        }
        .btn { padding: 10px 22px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; border: none; }
        .btn-print   { background: #0c3d8a; color: #fff; }
        .btn-back    { background: #fff; color: #64748b; border: 1px solid #e2e8f0; }
        .btn-print:hover { background: #4338ca; }
        .btn-back:hover  { background: #f8fafc; }

        /* ── ID Card ── */
        .id-card {
            width: 340px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,.12);
        }
        .card-header {
            background: linear-gradient(135deg, #0c3d8a, #1a6b3c);
            padding: 20px 24px 16px;
            text-align: center;
        }
        .card-header .school-name {
            font-size: 13px; font-weight: 800; color: rgba(255,255,255,.85);
            text-transform: uppercase; letter-spacing: .08em;
        }
        .card-header .card-title {
            font-size: 11px; color: rgba(255,255,255,.6);
            text-transform: uppercase; letter-spacing: .06em; margin-top: 2px;
        }
        .card-body { padding: 24px; text-align: center; }
        .qr-wrap {
            display: inline-block;
            padding: 10px; background: #fff;
            border: 3px solid #e2e8f0;
            border-radius: 16px; margin-bottom: 16px;
        }
        #qrCanvas { display: block; }
        .student-name {
            font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 4px;
        }
        .student-meta {
            font-size: 12px; color: #64748b; margin-bottom: 4px;
        }
        .student-id-badge {
            display: inline-block; background: #f1f5f9;
            border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 4px 14px; font-size: 13px; font-weight: 700;
            color: #334155; margin-top: 4px;
        }
        .card-footer {
            background: #f8fafc; border-top: 1px solid #e2e8f0;
            padding: 12px 24px; text-align: center;
            font-size: 11px; color: #94a3b8;
        }

        /* ── Print multiple cards on one sheet ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 340px);
            gap: 24px;
            justify-content: center;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none; }
            .id-card { box-shadow: none; border: 1px solid #e2e8f0; }
            .cards-grid { gap: 16px; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <button class="btn btn-print" onclick="window.print()">
        🖨️ Print Card
    </button>
    <button class="btn btn-back" onclick="history.back()">
        ← Back
    </button>
</div>

{{-- Render 2 copies side-by-side for paper efficiency --}}
<div class="cards-grid">
    @for($copy = 0; $copy < 2; $copy++)
    <div class="id-card">
        <div class="card-header">
            <div class="school-name">DMCMES Attendance System</div>
            <div class="card-title">Student Attendance QR Card</div>
        </div>
        <div class="card-body">
            <div class="qr-wrap">
                <div class="qr-card-canvas" id="qrCanvas{{ $copy }}" style="line-height:0;"></div>
            </div>
            <div class="student-name">{{ $student->user->name }}</div>
            <div class="student-meta">
                @if($student->grade_level) Grade {{ $student->grade_level }} @endif
                @if($student->section) &nbsp;·&nbsp; {{ $student->section }} @endif
            </div>
            <div class="student-id-badge">ID: {{ $student->student_id }}</div>
        </div>
        <div class="card-footer">
            Show this QR to the camera to mark attendance
        </div>
    </div>
    @endfor
</div>

<script>
const QR_DATA = @json($student->qrUrl());

// qrcodejs renders into each div container
document.querySelectorAll('.qr-card-canvas').forEach(div => {
    new QRCode(div, {
        text:         QR_DATA,
        width:        180,
        height:       180,
        colorDark:    '#0f172a',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
});
</script>
</body>
</html>
