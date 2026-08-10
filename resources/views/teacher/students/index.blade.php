@extends('layouts.app')
@section('title', 'My Students')
@section('page-title', 'My Students')

@push('styles')
<style>
/* ── Student row (mobile) ─────────────────────────────── */
.student-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-bottom: 1px solid #f1f5f9; min-width: 0;
}
.student-row:last-child { border-bottom: none; }
.student-row .sr-avatar {
    flex-shrink: 0; width: 42px; height: 42px; border-radius: 10px;
    overflow: hidden; background: linear-gradient(135deg,#0c3d8a,#1a6b3c);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #fff; font-weight: 700;
}
.student-row .sr-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.student-row .sr-info   { flex: 1; min-width: 0; }
.student-row .sr-name   { font-weight: 600; font-size: 14px; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-row .sr-sub    { font-size: 11px; color: #64748b; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; }
.student-row .sr-badge  { flex-shrink: 0; }
.student-row .sr-actions { flex-shrink: 0; display: flex; gap: 5px; align-items: center; }

/* ── Add Student modal ── */
#addModal .modal-header {
    background: linear-gradient(135deg,#0c3d8a,#1a6b3c); border: none;
}
#addModal .form-label { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
#addModal .form-control, #addModal .form-select { font-size: 13px; }
</style>
@endpush

@section('content')
<div class="row g-4">

    {{-- ── LEFT: My Students ── --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0">
                    <i class="bi bi-people-fill text-primary me-2"></i>My Class
                    <span class="badge bg-primary ms-2">{{ $students->count() }}</span>
                </h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Add Student
                </button>
            </div>
            <div class="card-body p-0">

                {{-- ── Desktop table ── --}}
                <div class="desk-list table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:52px;"></th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Grade</th>
                                <th>Section</th>
                                <th>Face</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                            <tr>
                                <td class="align-middle">
                                    <div style="width:38px;height:38px;border-radius:9px;overflow:hidden;background:linear-gradient(135deg,#0c3d8a,#1a6b3c);display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;font-weight:700;flex-shrink:0;"
                                         @if($student->face_registered && $student->face_encoding && Storage::disk('public')->exists($student->face_encoding))
                                             data-lightbox="{{ Storage::url($student->face_encoding) }}"
                                             data-lightbox-caption="{{ $student->user->name }}"
                                             data-lightbox-sub="Registered Face · {{ $student->student_id }}"
                                             style="cursor:zoom-in;"
                                         @endif>
                                        @if($student->face_registered && $student->face_encoding && Storage::disk('public')->exists($student->face_encoding))
                                            <img src="{{ Storage::url($student->face_encoding) }}" alt="{{ $student->user->name }}" style="width:100%;height:100%;object-fit:cover;display:block;">
                                        @else
                                            {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="fw-semibold" style="font-size:14px;">{{ $student->user->name }}</div>
                                    <div class="text-muted small">{{ $student->user->email }}</div>
                                </td>
                                <td class="align-middle text-muted small">{{ $student->student_id }}</td>
                                <td class="align-middle">{{ $student->grade_level ?? '—' }}</td>
                                <td class="align-middle">{{ $student->section ?? '—' }}</td>
                                <td class="align-middle">
                                    @if($student->face_registered)
                                        <span class="badge bg-success" style="font-size:10px;"><i class="bi bi-shield-fill-check me-1"></i>Done</span>
                                    @else
                                        <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                                    @endif
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="btn btn-sm btn-outline-info" title="View QR Card"
                                                onclick="showQr('{{ addslashes($student->user->name) }}','{{ $student->student_id }}','{{ addslashes($student->grade_level ?? '') }}','{{ addslashes($student->section ?? '') }}','{{ $student->qrUrl() }}')">
                                            <i class="bi bi-qr-code"></i>
                                        </button>
                                        <form action="{{ route('teacher.students.remove', $student) }}"
                                              method="POST" class="d-inline" onsubmit="return false"
                                              data-confirm-title="Remove Student"
                                              data-confirm-message="Remove {{ $student->user->name }} from your class?"
                                              data-confirm-ok="Remove" data-confirm-type="warning" data-confirm-icon="👤">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Remove from my class">
                                                <i class="bi bi-person-dash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-people" style="font-size:36px;display:block;margin-bottom:10px;opacity:.4;"></i>
                                    No students yet. Tap <strong>Add Student</strong> to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ── Mobile card rows ── --}}
                <div class="mob-list">
                    @forelse($students as $student)
                    <div class="student-row">
                        {{-- Avatar --}}
                        <div class="sr-avatar"
                             @if($student->face_registered && $student->face_encoding && Storage::disk('public')->exists($student->face_encoding))
                                 data-lightbox="{{ Storage::url($student->face_encoding) }}"
                                 data-lightbox-caption="{{ $student->user->name }}"
                                 data-lightbox-sub="Registered Face · {{ $student->student_id }}"
                                 style="cursor:zoom-in;"
                             @endif>
                            @if($student->face_registered && $student->face_encoding && Storage::disk('public')->exists($student->face_encoding))
                                <img src="{{ Storage::url($student->face_encoding) }}" alt="{{ $student->user->name }}">
                            @else
                                {{ strtoupper(substr($student->user->name, 0, 1)) }}
                            @endif
                        </div>
                        {{-- Info --}}
                        <div class="sr-info">
                            <div class="sr-name">{{ $student->user->name }}</div>
                            <div class="sr-sub">
                                {{ $student->user->email }}
                                @if($student->student_id) &middot; {{ $student->student_id }}@endif
                                @if($student->grade_level) &middot; {{ $student->grade_level }}@endif
                                @if($student->section) &middot; {{ $student->section }}@endif
                            </div>
                        </div>
                        {{-- Face badge --}}
                        <div class="sr-badge">
                            @if($student->face_registered)
                                <span class="badge bg-success" style="font-size:10px;"><i class="bi bi-shield-fill-check me-1"></i>Done</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                            @endif
                        </div>
                        {{-- Actions — always right --}}
                        <div class="sr-actions">
                            <button class="btn btn-sm btn-outline-info" title="View QR Card"
                                    onclick="showQr('{{ addslashes($student->user->name) }}','{{ $student->student_id }}','{{ addslashes($student->grade_level ?? '') }}','{{ addslashes($student->section ?? '') }}','{{ $student->qrUrl() }}')">
                                <i class="bi bi-qr-code"></i>
                            </button>
                            <form action="{{ route('teacher.students.remove', $student) }}"
                                  method="POST" class="d-inline" onsubmit="return false"
                                  data-confirm-title="Remove Student"
                                  data-confirm-message="Remove {{ $student->user->name }} from your class?"
                                  data-confirm-ok="Remove" data-confirm-type="warning" data-confirm-icon="👤">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Remove from my class">
                                    <i class="bi bi-person-dash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-people" style="font-size:36px;display:block;margin-bottom:10px;opacity:.4;"></i>
                        No students yet. Tap <strong>Add Student</strong> to get started.
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- Assign existing students --}}
        @if($available->count() > 0)
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-person-check-fill text-success me-2"></i>
                    Assign Existing Student
                    <span class="text-muted fw-normal small ms-1">(not yet in any class)</span>
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.students.assign') }}" method="POST" class="d-flex gap-2 flex-wrap">
                    @csrf
                    <select name="student_id" class="form-select form-select-sm" style="min-width:180px;flex:1;" required>
                        <option value="">— Select a student —</option>
                        @foreach($available as $s)
                            <option value="{{ $s->id }}">{{ $s->user->name }} ({{ $s->student_id }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-success text-nowrap">
                        <i class="bi bi-plus-circle me-1"></i> Add to My Class
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- ── RIGHT: Info Panel ── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i>About This Feature</h6>
                <p class="small text-muted mb-2">
                    Students in <strong>My Class</strong> are the only ones who can be marked
                    present in your sessions via face scan or QR code.
                </p>
                <p class="small text-muted mb-2">
                    <i class="bi bi-shield-fill-check text-success me-1"></i>
                    After adding a student, ask the admin to register their face so face scan works.
                </p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-qr-code text-info me-1"></i>
                    Each student has a unique QR card — tap the QR button to view and print it.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body text-center">
                <div style="font-size:34px;font-weight:800;color:#0c3d8a;">{{ $students->count() }}</div>
                <div class="text-muted small mb-3">Students in your class</div>
                <div style="font-size:28px;font-weight:800;color:#4ade80;">
                    {{ $students->where('face_registered', true)->count() }}
                </div>
                <div class="text-muted small">Faces registered</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Add Student Modal ── --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;max-height:90vh;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0c3d8a,#1a6b3c);border:none;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Add New Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher.students.store') }}" method="POST" id="addStudentForm" data-validate="true">
                @csrf
                <div class="modal-body p-4">
                    @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" data-label="Full Name" class="form-control" required
                               value="{{ old('name') }}" placeholder="Juan dela Cruz">
                    </div>

                    <div class="row g-2 mb-3" style="margin-left:0;margin-right:0;">
                        <div class="col-12" style="padding-left:calc(var(--bs-gutter-x) / 2);padding-right:calc(var(--bs-gutter-x) / 2);">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" data-label="Email" class="form-control" required
                                   value="{{ old('email') }}" placeholder="student@school.edu">
                        </div>
                    </div>

                    <div class="row g-2 mb-3" style="margin-left:0;margin-right:0;">
                        <div class="col-5" style="padding-left:calc(var(--bs-gutter-x) / 2);padding-right:calc(var(--bs-gutter-x) / 2);">
                            <label class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
                            <input type="text" name="student_id" data-label="Student ID" class="form-control" required
                                   value="{{ old('student_id') }}" placeholder="2024-0001">
                        </div>
                        <div class="col-4" style="padding-left:calc(var(--bs-gutter-x) / 2);padding-right:calc(var(--bs-gutter-x) / 2);">
                            <label class="form-label fw-semibold">Grade</label>
                            <input type="text" name="grade_level" class="form-control"
                                   value="{{ old('grade_level') }}" placeholder="Grade 7">
                        </div>
                        <div class="col-3" style="padding-left:calc(var(--bs-gutter-x) / 2);padding-right:calc(var(--bs-gutter-x) / 2);">
                            <label class="form-label fw-semibold">Section</label>
                            <input type="text" name="section" class="form-control"
                                   value="{{ old('section') }}" placeholder="A">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" data-label="Password" class="form-control" required minlength="6"
                               placeholder="At least 6 characters">
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addStudentForm" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill me-1"></i> Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── QR Modal ── --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:320px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#0c3d8a,#1a6b3c);padding:14px 20px;text-align:center;">
                <div style="font-size:11px;font-weight:800;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.08em;">DMCMES Attendance System</div>
                <div style="font-size:10px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Student QR Card</div>
            </div>
            <div class="modal-body text-center py-3">
                <div style="display:inline-block;padding:10px;border:3px solid #e2e8f0;border-radius:14px;margin-bottom:12px;line-height:0;">
                    <div id="qrDiv"></div>
                </div>
                <div id="qrStudentName" style="font-size:16px;font-weight:800;color:#0f172a;"></div>
                <div id="qrStudentMeta" style="font-size:12px;color:#64748b;margin-top:2px;"></div>
                <div id="qrStudentId" style="display:inline-block;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:3px 14px;font-size:12px;font-weight:700;color:#334155;margin-top:6px;"></div>
            </div>
            <div style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:8px 16px;text-align:center;font-size:11px;color:#94a3b8;">
                Show this QR to the camera to mark attendance
            </div>
            <div class="modal-footer justify-content-center py-2 gap-2">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-sm btn-primary" onclick="printQr()"><i class="bi bi-printer-fill me-1"></i>Print</button>
                <button class="btn btn-sm btn-outline-success" onclick="downloadQr()"><i class="bi bi-download me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let currentQr = {};

function showQr(name, sid, grade, section, url) {
    currentQr = { name, sid, grade, section, url };
    document.getElementById('qrStudentName').textContent = name;
    document.getElementById('qrStudentId').textContent   = 'ID: ' + sid;
    const meta = [grade ? 'Grade ' + grade : '', section].filter(Boolean).join(' · ');
    document.getElementById('qrStudentMeta').textContent = meta;

    const div = document.getElementById('qrDiv');
    div.innerHTML = '';
    new bootstrap.Modal(document.getElementById('qrModal')).show();

    setTimeout(() => {
        new QRCode(div, {
            text: url, width: 160, height: 160,
            colorDark: '#0f172a', colorLight: '#fff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }, 80);
}

function getQrDataUrl() {
    const img = document.querySelector('#qrDiv img');
    return img?.src || null;
}

function downloadQr() {
    setTimeout(() => {
        const url = getQrDataUrl();
        if (!url) return;
        const a = document.createElement('a');
        a.download = `qr-${currentQr.sid}.png`;
        a.href = url; a.click();
    }, 100);
}

function printQr() {
    setTimeout(() => {
        const url = getQrDataUrl();
        if (!url) return;
        const { name, sid, grade, section } = currentQr;
        const meta = [grade ? 'Grade ' + grade : '', section].filter(Boolean).join(' · ');
        const card = `<div class="card"><div class="ch"><div class="s">DMCMES Attendance System</div><div class="t">Student QR Card</div></div><div class="cb"><div class="qw"><img src="${url}"></div><div class="sn">${name}</div><div class="sm">${meta}</div><div class="si">ID: ${sid}</div></div><div class="cf">Show this QR to the camera to mark attendance</div></div>`;
        const win = window.open('', '_blank', 'width=640,height=500');
        win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>QR — ${name}</title><style>*{box-sizing:border-box;margin:0;padding:0;}body{background:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;font-family:sans-serif;}.grid{display:grid;grid-template-columns:repeat(2,260px);gap:16px;}.card{border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;}.ch{background:linear-gradient(135deg,#0c3d8a,#1a6b3c);padding:12px;text-align:center;}.s{font-size:10px;font-weight:800;color:rgba(255,255,255,.9);text-transform:uppercase;letter-spacing:.08em;}.t{font-size:9px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;}.cb{padding:14px;text-align:center;}.qw{display:inline-block;padding:6px;border:2px solid #e2e8f0;border-radius:10px;margin-bottom:8px;}.qw img{display:block;width:130px;height:130px;}.sn{font-size:13px;font-weight:800;color:#0f172a;margin-bottom:2px;}.sm{font-size:11px;color:#64748b;}.si{display:inline-block;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:2px 10px;font-size:10px;font-weight:700;color:#334155;margin-top:4px;}.cf{background:#f8fafc;border-top:1px solid #e2e8f0;padding:7px;text-align:center;font-size:9px;color:#94a3b8;}@media print{body{padding:0;}@page{margin:8mm;}}</style></head><body><div class="grid">${card}${card}</div><script>window.onload=()=>setTimeout(()=>window.print(),150);<\/script></body></html>`);
        win.document.close();
    }, 100);
}

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('addModal')).show();
});
@endif
</script>
@endpush
