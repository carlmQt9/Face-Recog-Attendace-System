@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@push('styles')
<style>
/* ── QR Modal Backdrop ──────────────────────────────────── */
.qr-backdrop {
    position: fixed; inset: 0; z-index: 1055;
    background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
}
.qr-backdrop.open { opacity: 1; pointer-events: all; }

/* ── QR Modal Box ───────────────────────────────────────── */
.qr-modal {
    background: #fff; border-radius: 20px;
    overflow: hidden; width: 320px;
    box-shadow: 0 24px 80px rgba(0,0,0,.25);
    transform: scale(.92); transition: transform .25s cubic-bezier(.34,1.56,.64,1);
}
.qr-backdrop.open .qr-modal { transform: scale(1); }

.qr-modal-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 18px 20px 14px; text-align: center;
}
.qr-modal-header .school  { font-size: 12px; font-weight: 800; color: rgba(255,255,255,.9); text-transform: uppercase; letter-spacing: .08em; }
.qr-modal-header .subtitle{ font-size: 10px; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .06em; margin-top: 2px; }

.qr-modal-body { padding: 20px; text-align: center; }
.qr-canvas-wrap {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 10px; background: #fff;
    border: 3px solid #e2e8f0; border-radius: 14px;
    margin-bottom: 14px;
}
/* qrcodejs renders a table or canvas inside the div */
#qrModalCanvas { line-height: 0; }
#qrModalCanvas img,
#qrModalCanvas canvas { display: block; }

.qr-student-name { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 3px; }
.qr-student-meta { font-size: 12px; color: #64748b; margin-bottom: 6px; }
.qr-id-badge {
    display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 3px 14px;
    font-size: 12px; font-weight: 700; color: #334155;
}

.qr-modal-footer {
    background: #f8fafc; border-top: 1px solid #e2e8f0;
    padding: 10px 20px 14px; text-align: center;
}
.qr-modal-footer .hint { font-size: 11px; color: #94a3b8; margin-bottom: 10px; }
.qr-actions { display: flex; gap: 8px; justify-content: center; }
.qr-actions .btn { border-radius: 9px; font-size: 13px; font-weight: 600; padding: 7px 18px; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $faceUrl = null;
                                if ($user->role === 'student' && $user->student?->face_registered && $user->student?->face_encoding) {
                                    if (Storage::disk('public')->exists($user->student->face_encoding)) {
                                        $faceUrl = Storage::url($user->student->face_encoding);
                                    }
                                } elseif ($user->role === 'teacher' && $user->teacher?->face_registered && $user->teacher?->face_encoding) {
                                    if (Storage::disk('public')->exists($user->teacher->face_encoding)) {
                                        $faceUrl = Storage::url($user->teacher->face_encoding);
                                    }
                                }
                            @endphp
                            @if($faceUrl)
                                <img src="{{ $faceUrl }}"
                                     alt="{{ $user->name }}"
                                     data-lightbox="{{ $faceUrl }}"
                                     data-lightbox-caption="{{ $user->name }}"
                                     data-lightbox-sub="{{ ucfirst($user->role) }}"
                                     style="width:38px;height:38px;border-radius:9px;object-fit:cover;flex-shrink:0;border:1px solid #dee2e6;cursor:zoom-in;">
                            @else
                                <div style="width:38px;height:38px;border-radius:9px;flex-shrink:0;
                                            background:linear-gradient(135deg,#4f46e5,#06b6d4);
                                            display:flex;align-items:center;justify-content:center;
                                            font-size:15px;color:#fff;font-weight:700;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="fw-semibold">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'success' : 'primary') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                        {{-- QR button — only for students with a token --}}
                        @if($user->role === 'student' && $user->student?->qr_token)
                            <button class="btn btn-sm btn-outline-info"
                                    title="View & Print QR Card"
                                    onclick="openQrModal(
                                        '{{ addslashes($user->name) }}',
                                        '{{ addslashes($user->student->student_id) }}',
                                        '{{ addslashes($user->student->grade_level ?? '') }}',
                                        '{{ addslashes($user->student->section ?? '') }}',
                                        '{{ $user->student->qrUrl() }}'
                                    )">
                                <i class="bi bi-qr-code"></i> QR
                            </button>
                        @endif

                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              class="d-inline"
                              onsubmit="return false"
                              data-confirm-title="Delete User"
                              data-confirm-message="Are you sure you want to permanently delete {{ $user->name }}? All their data will be removed."
                              data-confirm-ok="Delete"
                              data-confirm-type="danger"
                              data-confirm-icon="🗑️">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $users->links() }}

{{-- ── QR Modal ── --}}
<div class="qr-backdrop" id="qrBackdrop" onclick="closeQrOutside(event)">
    <div class="qr-modal" id="qrModal">

        <div class="qr-modal-header">
            <div class="school">Face Attendance System</div>
            <div class="subtitle">Student Attendance QR Card</div>
        </div>

        <div class="qr-modal-body">
            <div class="qr-canvas-wrap">
                <div id="qrModalCanvas"></div>
            </div>
            <div class="qr-student-name" id="qrName">—</div>
            <div class="qr-student-meta" id="qrMeta"></div>
            <div class="qr-id-badge" id="qrIdBadge"></div>
        </div>

        <div class="qr-modal-footer">
            <div class="hint">Show this QR to the camera to mark attendance</div>
            <div class="qr-actions">
                <button class="btn btn-outline-secondary btn-sm" onclick="closeQr()">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
                <button class="btn btn-primary btn-sm" onclick="printQrCard()">
                    <i class="bi bi-printer-fill me-1"></i>Print
                </button>
                <button class="btn btn-outline-success btn-sm" onclick="downloadQr()">
                    <i class="bi bi-download me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let currentQrData = {};
let qrInstance    = null;   // holds the QRCode object for reuse

// ── Open modal and render QR ───────────────────────────────────────────────────
function openQrModal(name, studentId, grade, section, qrUrl) {
    currentQrData = { name, studentId, grade, section, qrUrl };

    // Fill in text
    document.getElementById('qrName').textContent    = name;
    document.getElementById('qrIdBadge').textContent = 'ID: ' + studentId;
    const metaParts = [];
    if (grade)   metaParts.push('Grade ' + grade);
    if (section) metaParts.push(section);
    document.getElementById('qrMeta').textContent = metaParts.join(' · ');

    // Show modal
    document.getElementById('qrBackdrop').classList.add('open');

    // Clear previous QR and render new one after the modal is visible
    const container = document.getElementById('qrModalCanvas');
    container.innerHTML = '';   // wipe old render

    // Small delay so the modal's display transition settles
    setTimeout(() => {
        qrInstance = new QRCode(container, {
            text:           qrUrl,
            width:          160,
            height:         160,
            colorDark:      '#0f172a',
            colorLight:     '#ffffff',
            correctLevel:   QRCode.CorrectLevel.H
        });
    }, 80);
}

function closeQr() {
    document.getElementById('qrBackdrop').classList.remove('open');
}

function closeQrOutside(e) {
    if (e.target === document.getElementById('qrBackdrop')) closeQr();
}

// ── Get the rendered QR as a data URL (qrcodejs puts an <img> or <canvas> inside) ──
function getQrDataUrl() {
    const container = document.getElementById('qrModalCanvas');
    // qrcodejs renders an <img> tag with a data URI src
    const img = container.querySelector('img');
    if (img && img.src) return img.src;
    // fallback: try canvas
    const cvs = container.querySelector('canvas');
    if (cvs) return cvs.toDataURL('image/png');
    return null;
}

// ── Download QR as PNG ────────────────────────────────────────────────────────
function downloadQr() {
    // Give qrcodejs a moment to finish rendering if called quickly
    setTimeout(() => {
        const dataUrl = getQrDataUrl();
        if (!dataUrl) { alert('QR not ready yet, please wait a moment.'); return; }
        const a = document.createElement('a');
        a.download = `qr-${currentQrData.studentId || 'student'}.png`;
        a.href = dataUrl;
        a.click();
    }, 100);
}

// ── Print: 2-up card layout in a new window ───────────────────────────────────
function printQrCard() {
    setTimeout(() => {
        const dataUrl = getQrDataUrl();
        if (!dataUrl) { alert('QR not ready yet, please wait a moment.'); return; }

        const { name, studentId, grade, section } = currentQrData;
        const metaParts = [];
        if (grade)   metaParts.push('Grade ' + grade);
        if (section) metaParts.push(section);
        const meta = metaParts.join(' · ');

        const cardHtml = `
<div class="card">
  <div class="card-head">
    <div class="s">Face Attendance System</div>
    <div class="t">Student Attendance QR Card</div>
  </div>
  <div class="card-body">
    <div class="qr-wrap"><img src="${dataUrl}" alt="QR Code"></div>
    <div class="sname">${name}</div>
    <div class="smeta">${meta}</div>
    <div class="sid">ID: ${studentId}</div>
  </div>
  <div class="card-foot">Show this QR to the camera to mark attendance</div>
</div>`;

        const win = window.open('', '_blank', 'width=700,height=520');
        win.document.write(`<!DOCTYPE html><html><head>
<meta charset="UTF-8">
<title>QR Card — ${name}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#fff;display:flex;align-items:center;justify-content:center;
     min-height:100vh;padding:24px;font-family:'Segoe UI',sans-serif;}
.grid{display:grid;grid-template-columns:repeat(2,270px);gap:18px;}
.card{border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;
      box-shadow:0 4px 16px rgba(0,0,0,.08);}
.card-head{background:linear-gradient(135deg,#4f46e5,#06b6d4);
           padding:13px 16px 10px;text-align:center;}
.s{font-size:10px;font-weight:800;color:rgba(255,255,255,.9);
   text-transform:uppercase;letter-spacing:.08em;}
.t{font-size:9px;color:rgba(255,255,255,.55);text-transform:uppercase;
   letter-spacing:.06em;margin-top:2px;}
.card-body{padding:16px;text-align:center;}
.qr-wrap{display:inline-block;padding:8px;border:2px solid #e2e8f0;
         border-radius:10px;margin-bottom:10px;}
.qr-wrap img{display:block;width:136px;height:136px;}
.sname{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:2px;}
.smeta{font-size:11px;color:#64748b;margin-bottom:5px;}
.sid{display:inline-block;background:#f1f5f9;border:1px solid #e2e8f0;
     border-radius:6px;padding:2px 12px;font-size:11px;font-weight:700;color:#334155;}
.card-foot{background:#f8fafc;border-top:1px solid #e2e8f0;
           padding:8px 16px;text-align:center;font-size:10px;color:#94a3b8;}
@media print{body{padding:0;}@page{margin:10mm;size:A4;}}
</style></head><body>
<div class="grid">${cardHtml}${cardHtml}</div>
<script>window.onload=()=>{ setTimeout(()=>window.print(),200); }<\/script>
</body></html>`);
        win.document.close();
    }, 100);
}

// ── Auto-open QR modal for newly created student ──────────────────────────────
@if(session('new_student_qr'))
    @php $nq = session('new_student_qr'); @endphp
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => openQrModal(
            @json($nq['name']),
            @json($nq['student_id']),
            @json($nq['grade_level'] ?? ''),
            @json($nq['section'] ?? ''),
            @json($nq['qr_url'])
        ), 400);
    });
@endif</script>
@endpush
