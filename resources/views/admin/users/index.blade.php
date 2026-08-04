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
    padding: 16px;
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
    overflow-y: auto;
}
.qr-backdrop.open { opacity: 1; pointer-events: all; }

.qr-modal {
    background: #fff; border-radius: 20px;
    overflow: hidden; width: min(320px, 100%);
    box-shadow: 0 24px 80px rgba(0,0,0,.25);
    transform: scale(.92); transition: transform .25s cubic-bezier(.34,1.56,.64,1);
    max-height: calc(100dvh - 32px); overflow-y: auto;
}
.qr-backdrop.open .qr-modal { transform: scale(1); }
.qr-modal-header { background: linear-gradient(135deg, #4f46e5, #06b6d4); padding: 18px 20px 14px; text-align: center; }
.qr-modal-header .school   { font-size: 12px; font-weight: 800; color: rgba(255,255,255,.9); text-transform: uppercase; letter-spacing: .08em; }
.qr-modal-header .subtitle { font-size: 10px; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .06em; margin-top: 2px; }
.qr-modal-body { padding: 20px; text-align: center; }
.qr-canvas-wrap { display: inline-flex; align-items: center; justify-content: center; padding: 10px; border: 3px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; }
#qrModalCanvas { line-height: 0; }
#qrModalCanvas img, #qrModalCanvas canvas { display: block; }
.qr-student-name { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 3px; }
.qr-student-meta { font-size: 12px; color: #64748b; margin-bottom: 6px; }
.qr-id-badge { display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 3px 14px; font-size: 12px; font-weight: 700; color: #334155; }
.qr-modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 20px 14px; text-align: center; }
.qr-modal-footer .hint { font-size: 11px; color: #94a3b8; margin-bottom: 10px; }
.qr-actions { display: flex; gap: 8px; justify-content: center; }
.qr-actions .btn { border-radius: 9px; font-size: 13px; font-weight: 600; padding: 7px 18px; }

/* ── User table row — actions always right-aligned ── */
/* Mobile only — shown via .mob-list / .desk-list in app.blade.php */
.user-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-bottom: 1px solid #f1f5f9; min-width: 0;
}
.user-row:last-child { border-bottom: none; }
.user-row .u-avatar  { flex-shrink: 0; }
.user-row .u-info    { flex: 1; min-width: 0; }
.user-row .u-name    { font-weight: 600; font-size: 14px; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-row .u-email   { font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-row .u-badge   { flex-shrink: 0; }
.user-row .u-actions { flex-shrink: 0; display: flex; gap: 5px; align-items: center; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus-fill me-1"></i> Add User
    </button>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- ── DESKTOP: normal table ── --}}
        <div class="desk-list table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:46px;"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="d-none d-md-table-cell">Created</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $faceUrl = null;
                    if ($user->role === 'student' && $user->student?->face_registered && $user->student?->face_encoding) {
                        if (Storage::disk('public')->exists($user->student->face_encoding)) $faceUrl = Storage::url($user->student->face_encoding);
                    } elseif ($user->role === 'teacher' && $user->teacher?->face_registered && $user->teacher?->face_encoding) {
                        if (Storage::disk('public')->exists($user->teacher->face_encoding)) $faceUrl = Storage::url($user->teacher->face_encoding);
                    }
                @endphp
                <tr>
                    <td>
                        @if($faceUrl)
                            <img src="{{ $faceUrl }}" alt="{{ $user->name }}" data-lightbox="{{ $faceUrl }}" data-lightbox-caption="{{ $user->name }}" data-lightbox-sub="{{ ucfirst($user->role) }}" style="width:38px;height:38px;border-radius:9px;object-fit:cover;border:1px solid #dee2e6;cursor:zoom-in;">
                        @else
                            <div style="width:38px;height:38px;border-radius:9px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;font-weight:700;">{{ strtoupper(substr($user->name,0,1)) }}</div>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $user->name }}</td>
                    <td class="text-muted small">{{ $user->email }}</td>
                    <td><span class="badge bg-{{ $user->role==='admin'?'danger':($user->role==='teacher'?'success':'primary') }}">{{ ucfirst($user->role) }}</span></td>
                    <td class="d-none d-md-table-cell text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1" style="white-space:nowrap;">
                            <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="openEditModal({{ $user->id }},'{{ addslashes($user->name) }}','{{ addslashes($user->email) }}','{{ $user->role }}','{{ addslashes($user->student?->student_id??'') }}','{{ addslashes($user->student?->grade_level??'') }}','{{ addslashes($user->student?->section??'') }}','{{ addslashes($user->teacher?->employee_id??'') }}','{{ addslashes($user->teacher?->department??'') }}')"><i class="bi bi-pencil-fill"></i></button>
                            @if($user->role==='student'&&$user->student?->qr_token)
                                <button class="btn btn-sm btn-outline-info" title="QR Card" onclick="openQrModal('{{ addslashes($user->name) }}','{{ addslashes($user->student->student_id) }}','{{ addslashes($user->student->grade_level??'') }}','{{ addslashes($user->student->section??'') }}','{{ $user->student->qrUrl() }}')"><i class="bi bi-qr-code"></i></button>
                            @endif
                            @if(auth()->id()!==$user->id)
                            <form action="{{ route('admin.users.destroy',$user) }}" method="POST" class="d-inline" onsubmit="return false" data-confirm-title="Delete User" data-confirm-message="Permanently delete {{ $user->name }}? All data will be removed." data-confirm-ok="Delete" data-confirm-type="danger" data-confirm-icon="🗑️">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- ── MOBILE: flex card rows ── --}}
        <div class="mob-list">
            @forelse($users as $user)
            @php
                $faceUrl = null;
                if ($user->role === 'student' && $user->student?->face_registered && $user->student?->face_encoding) {
                    if (Storage::disk('public')->exists($user->student->face_encoding)) $faceUrl = Storage::url($user->student->face_encoding);
                } elseif ($user->role === 'teacher' && $user->teacher?->face_registered && $user->teacher?->face_encoding) {
                    if (Storage::disk('public')->exists($user->teacher->face_encoding)) $faceUrl = Storage::url($user->teacher->face_encoding);
                }
            @endphp
            <div class="user-row">
                <div class="u-avatar">
                    @if($faceUrl)
                        <img src="{{ $faceUrl }}" alt="{{ $user->name }}" data-lightbox="{{ $faceUrl }}" data-lightbox-caption="{{ $user->name }}" data-lightbox-sub="{{ ucfirst($user->role) }}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #dee2e6;cursor:zoom-in;">
                    @else
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;font-weight:700;">{{ strtoupper(substr($user->name,0,1)) }}</div>
                    @endif
                </div>
                <div class="u-info">
                    <div class="u-name">{{ $user->name }}</div>
                    <div class="u-email">{{ $user->email }}</div>
                </div>
                <div class="u-badge">
                    <span class="badge bg-{{ $user->role==='admin'?'danger':($user->role==='teacher'?'success':'primary') }}">{{ ucfirst($user->role) }}</span>
                </div>
                <div class="u-actions">
                    <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="openEditModal({{ $user->id }},'{{ addslashes($user->name) }}','{{ addslashes($user->email) }}','{{ $user->role }}','{{ addslashes($user->student?->student_id??'') }}','{{ addslashes($user->student?->grade_level??'') }}','{{ addslashes($user->student?->section??'') }}','{{ addslashes($user->teacher?->employee_id??'') }}','{{ addslashes($user->teacher?->department??'') }}')"><i class="bi bi-pencil-fill"></i></button>
                    @if($user->role==='student'&&$user->student?->qr_token)
                        <button class="btn btn-sm btn-outline-info" title="QR Card" onclick="openQrModal('{{ addslashes($user->name) }}','{{ addslashes($user->student->student_id) }}','{{ addslashes($user->student->grade_level??'') }}','{{ addslashes($user->student->section??'') }}','{{ $user->student->qrUrl() }}')"><i class="bi bi-qr-code"></i></button>
                    @endif
                    @if(auth()->id()!==$user->id)
                    <form action="{{ route('admin.users.destroy',$user) }}" method="POST" class="d-inline" onsubmit="return false" data-confirm-title="Delete User" data-confirm-message="Permanently delete {{ $user->name }}? All data will be removed." data-confirm-ok="Delete" data-confirm-type="danger" data-confirm-icon="🗑️">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">No users found.</div>
            @endforelse
        </div>

    </div>
</div>
{{ $users->links() }}

{{-- ── Add User Modal ── --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);border:none;">
                <h5 class="modal-title text-white fw-bold" id="addUserModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm" data-validate="true">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" data-label="Full Name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Juan dela Cruz">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" data-label="Email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="user@school.edu">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" data-label="Password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" data-label="Confirm Password"
                                   class="form-control" placeholder="Repeat password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="modalRoleSelect" data-label="Role"
                                    class="form-select @error('role') is-invalid @enderror"
                                    onchange="toggleModalFields()">
                                <option value="">— Select Role —</option>
                                <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Teacher fields --}}
                        <div id="modalTeacherFields" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Employee ID <span class="text-danger">*</span></label>
                                    <input type="text" name="employee_id" data-label="Employee ID"
                                           class="form-control @error('employee_id') is-invalid @enderror"
                                           value="{{ old('employee_id') }}" placeholder="EMP-001">
                                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Department</label>
                                    <input type="text" name="department" class="form-control"
                                           value="{{ old('department') }}" placeholder="e.g. Science">
                                </div>
                            </div>
                        </div>

                        {{-- Student fields --}}
                        <div id="modalStudentFields" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id" data-label="Student ID"
                                           class="form-control @error('student_id') is-invalid @enderror"
                                           value="{{ old('student_id') }}" placeholder="2024-0001">
                                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Grade Level</label>
                                    <input type="text" name="grade_level" class="form-control"
                                           value="{{ old('grade_level') }}" placeholder="e.g. Grade 7">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Section</label>
                                    <input type="text" name="section" class="form-control"
                                           value="{{ old('section') }}" placeholder="e.g. Abakada">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:14px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addUserForm" class="btn btn-primary px-4">
                    <i class="bi bi-person-plus-fill me-1"></i> Create User
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit User Modal ── --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0891b2,#4f46e5);border:none;">
                <h5 class="modal-title text-white fw-bold" id="editUserModalLabel">
                    <i class="bi bi-pencil-fill me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editUserForm" method="POST" data-validate="true">
                    @csrf @method('PUT')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" data-label="Full Name" class="form-control" required placeholder="e.g. Juan dela Cruz">
                    </div>
                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" data-label="Email" class="form-control" required placeholder="user@school.edu">
                    </div>
                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            New Password
                            <span class="text-muted fw-normal" style="font-size:12px;">(leave blank to keep)</span>
                        </label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password">
                    </div>

                    {{-- Role-specific fields (read-only display) --}}
                    <div id="editStudentFields" class="d-none">
                        <hr class="my-2">
                        <p class="text-muted small mb-2"><i class="bi bi-person-badge me-1"></i>Student Details</p>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Student ID</label>
                                <input type="text" name="student_id" id="editStudentId" class="form-control" placeholder="2024-0001">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Grade Level</label>
                                <input type="text" name="grade_level" id="editGradeLevel" class="form-control" placeholder="e.g. Grade 7">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Section</label>
                                <input type="text" name="section" id="editSection" class="form-control" placeholder="e.g. Abakada">
                            </div>
                        </div>
                    </div>
                    <div id="editTeacherFields" class="d-none">
                        <hr class="my-2">
                        <p class="text-muted small mb-2"><i class="bi bi-person-workspace me-1"></i>Teacher Details</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Employee ID</label>
                                <input type="text" name="employee_id" id="editEmployeeId" class="form-control" placeholder="EMP-001">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" id="editDepartment" class="form-control" placeholder="e.g. Science">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editUserForm" class="btn btn-primary px-4">
                    <i class="bi bi-floppy-fill me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

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
@endif

// ── Add User modal role toggle ────────────────────────────────────────────────
function toggleModalFields() {
    const role = document.getElementById('modalRoleSelect').value;
    document.getElementById('modalTeacherFields').classList.toggle('d-none', role !== 'teacher');
    document.getElementById('modalStudentFields').classList.toggle('d-none', role !== 'student');
}

// ── Edit User modal ───────────────────────────────────────────────────────────
function openEditModal(id, name, email, role, studentId, gradeLevel, section, employeeId, department) {
    // Set form action to the correct PUT route
    const form = document.getElementById('editUserForm');
    form.action = `/admin/users/${id}`;

    // Fill common fields
    document.getElementById('editName').value  = name;
    document.getElementById('editEmail').value = email;
    // Clear password fields
    form.querySelector('[name="password"]').value = '';
    form.querySelector('[name="password_confirmation"]').value = '';

    // Show/hide role-specific fields
    const stuFields = document.getElementById('editStudentFields');
    const tchrFields = document.getElementById('editTeacherFields');
    stuFields.classList.add('d-none');
    tchrFields.classList.add('d-none');

    if (role === 'student') {
        document.getElementById('editStudentId').value   = studentId;
        document.getElementById('editGradeLevel').value  = gradeLevel;
        document.getElementById('editSection').value     = section;
        stuFields.classList.remove('d-none');
    } else if (role === 'teacher') {
        document.getElementById('editEmployeeId').value  = employeeId;
        document.getElementById('editDepartment').value  = department;
        tchrFields.classList.remove('d-none');
    }

    // Open the modal
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

document.addEventListener('DOMContentLoaded', () => {
    // Re-run toggle on load for old() values
    toggleModalFields();

    // Auto-open modal if validation errors exist (form was submitted with errors)
    @if($errors->any())
        const addModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addModal.show();
    @endif
});</script>
@endpush
