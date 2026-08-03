<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Attendance System') | Face Recognition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .sidebar { min-height: 100vh; background: #1a1a2e; color: #fff; width: 240px; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar .brand { padding: 20px 16px; font-size: 16px; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .nav-link { color: rgba(255,255,255,0.75); padding: 10px 16px; display: flex; align-items: center; gap: 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); border-radius: 6px; }
        .main-content { margin-left: 240px; padding: 24px; }
        .top-bar { background: #fff; padding: 12px 24px; margin: -24px -24px 24px; display: flex; justify-content: between; align-items: center; border-bottom: 1px solid #e0e0e0; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .stat-card { border-radius: 12px; padding: 20px; color: #fff; }
        .badge-role { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        /* ── Pagination ── */
        .pagination { flex-wrap:wrap; gap:4px; }
        .pagination .page-link {
            border-radius:8px !important;
            border-color:#e2e8f0;
            color:#475569;
            font-size:13px;
            padding:6px 12px;
        }
        .pagination .page-item.active .page-link {
            background:#4f46e5;
            border-color:#4f46e5;
        }
        .pagination .page-item.disabled .page-link { color:#cbd5e1; }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="brand">
            <i class="bi bi-camera-fill me-2 text-primary"></i>
            Face Attendance
        </div>
        <nav class="p-2 mt-2">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Users
                    </a>
                    <a href="{{ route('admin.face-registration.index') }}" class="nav-link {{ request()->is('admin/face-registration*') ? 'active' : '' }}">
                        <i class="bi bi-person-bounding-box"></i> Face Registration
                    </a>
                    <a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->is('admin/parents*') ? 'active' : '' }}">
                        <i class="bi bi-heart-fill"></i> Parent Setup
                    </a>
                    <a href="{{ route('admin.cameras.index') }}" class="nav-link {{ request()->is('admin/cameras*') ? 'active' : '' }}">
                        <i class="bi bi-camera-video-fill"></i> Cameras
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" class="nav-link {{ request()->is('admin/attendance*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check-fill"></i> Attendance Archive
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <i class="bi bi-gear-fill"></i> System Settings
                    </a>

                @elseif(auth()->user()->isTeacher())
                    <a href="{{ route('teacher.sessions.index') }}" class="nav-link {{ request()->is('teacher/sessions*') ? 'active' : '' }}">
                        <i class="bi bi-camera-video-fill"></i> My Sessions
                    </a>
                    <a href="{{ route('teacher.students.index') }}" class="nav-link {{ request()->is('teacher/students*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> My Students
                    </a>

                @elseif(auth()->user()->isStudent())
                    <a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->is('student*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i> My Attendance
                    </a>
                @endif

                <hr style="border-color:rgba(255,255,255,0.1);">
                <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                    @csrf
                    <button type="button"
                            class="nav-link border-0 bg-transparent w-100 text-start"
                            onclick="handleLogout()">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            @endauth
        </nav>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            @auth
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary badge-role">{{ auth()->user()->role }}</span>
                    <span class="text-muted small">{{ auth()->user()->name }}</span>
                </div>
            @endauth
        </div>

        {{-- Flash messages --}}
        @foreach(['success', 'warning', 'danger', 'info'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach
        {{-- 'error' key maps to danger --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

    <!-- ══════════ GLOBAL IMAGE LIGHTBOX ══════════ -->
    <style>
        #imgLightbox {
            position:fixed; inset:0; z-index:99999;
            background:rgba(0,0,0,0.88);
            display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:14px;
            opacity:0; pointer-events:none;
            transition:opacity .22s ease;
            cursor:zoom-out;
        }
        #imgLightbox.show { opacity:1; pointer-events:all; }
        #imgLightboxImg {
            max-width:min(90vw,520px);
            max-height:75vh;
            border-radius:18px;
            box-shadow:0 32px 80px rgba(0,0,0,.7);
            object-fit:contain;
            transform:scale(0.85);
            transition:transform .28s cubic-bezier(.34,1.56,.64,1);
            display:block;
        }
        #imgLightbox.show #imgLightboxImg { transform:scale(1); }
        #imgLightboxCaption {
            color:#e2e8f0; font-size:15px; font-weight:700;
            text-align:center; letter-spacing:.01em;
        }
        #imgLightboxSub {
            color:#64748b; font-size:13px; text-align:center; margin-top:-8px;
        }
        #imgLightboxClose {
            position:fixed; top:18px; right:20px;
            width:40px; height:40px; border-radius:50%;
            background:rgba(255,255,255,.12); border:none;
            color:#fff; font-size:20px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            transition:background .18s;
        }
        #imgLightboxClose:hover { background:rgba(255,255,255,.25); }

        /* Make any element with data-lightbox clickable */
        [data-lightbox] {
            cursor:zoom-in !important;
            transition:transform .18s, box-shadow .18s;
        }
        [data-lightbox]:hover {
            transform:scale(1.07) !important;
            box-shadow:0 0 0 3px rgba(99,102,241,.6) !important;
        }
    </style>

    <div id="imgLightbox" onclick="closeLightbox()">
        <button id="imgLightboxClose" onclick="closeLightbox()" title="Close (Esc)">✕</button>
        <img id="imgLightboxImg" src="" alt="">
        <div id="imgLightboxCaption"></div>
        <div id="imgLightboxSub"></div>
    </div>

    <script>
    function openLightbox(src, caption, sub) {
        document.getElementById('imgLightboxImg').src        = src;
        document.getElementById('imgLightboxCaption').textContent = caption || '';
        document.getElementById('imgLightboxSub').textContent     = sub     || '';
        document.getElementById('imgLightbox').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('imgLightbox').classList.remove('show');
        document.body.style.overflow = '';
        setTimeout(() => { document.getElementById('imgLightboxImg').src = ''; }, 250);
    }
    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && document.getElementById('imgLightbox').classList.contains('show')) {
            closeLightbox();
        }
    });
    // Auto-wire all [data-lightbox] images on click
    document.addEventListener('click', e => {
        const el = e.target.closest('[data-lightbox]');
        if (!el) return;
        e.stopPropagation();
        openLightbox(
            el.dataset.lightbox,
            el.dataset.lightboxCaption || '',
            el.dataset.lightboxSub     || ''
        );
    });
    </script>

    <!-- ══════════ GLOBAL FLOATING CONFIRM MODAL ══════════ -->
    <style>
        #confirmModalBackdrop {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .22s ease;
        }
        #confirmModalBackdrop.show { opacity: 1; pointer-events: all; }
        #confirmModalBox {
            background: #fff;
            border-radius: 18px;
            padding: 32px 28px 24px;
            min-width: 320px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 24px 60px rgba(0,0,0,0.25), 0 4px 16px rgba(0,0,0,0.12);
            transform: scale(0.88) translateY(16px);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1), opacity .22s ease;
            opacity: 0;
        }
        #confirmModalBackdrop.show #confirmModalBox {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        #confirmModalIcon {
            font-size: 38px;
            margin-bottom: 12px;
            display: block;
            text-align: center;
        }
        #confirmModalTitle {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            margin-bottom: 8px;
        }
        #confirmModalMessage {
            font-size: 14px;
            color: #64748b;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .confirm-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        #confirmModalCancel {
            flex: 1;
            padding: 10px 0;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        #confirmModalCancel:hover { background: #f1f5f9; border-color: #cbd5e1; }
        #confirmModalOk {
            flex: 1;
            padding: 10px 0;
            border-radius: 10px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
        }
        #confirmModalOk:hover { opacity: .88; transform: translateY(-1px); }
        #confirmModalOk.danger  { background: linear-gradient(135deg,#dc2626,#f87171); color:#fff; }
        #confirmModalOk.warning { background: linear-gradient(135deg,#d97706,#fbbf24); color:#fff; }
        #confirmModalOk.primary { background: linear-gradient(135deg,#4f46e5,#818cf8); color:#fff; }
        #confirmModalOk.success { background: linear-gradient(135deg,#059669,#34d399); color:#fff; }
    </style>

    <!-- Modal HTML -->
    <div id="confirmModalBackdrop">
        <div id="confirmModalBox">
            <span id="confirmModalIcon">⚠️</span>
            <div id="confirmModalTitle">Are you sure?</div>
            <div id="confirmModalMessage">This action cannot be undone.</div>
            <div class="confirm-modal-actions">
                <button id="confirmModalCancel" onclick="confirmModalResolve(false)">Cancel</button>
                <button id="confirmModalOk"     onclick="confirmModalResolve(true)">Confirm</button>
            </div>
        </div>
    </div>

    <script>
    let confirmModalResolve = null;

    /**
     * showConfirm({ title, message, okText, okType, icon })
     * okType: 'danger' | 'warning' | 'primary' | 'success'
     * Returns a Promise<boolean>
     */
    function showConfirm({ title = 'Are you sure?', message = 'This action cannot be undone.', okText = 'Confirm', okType = 'danger', icon = '⚠️' } = {}) {
        document.getElementById('confirmModalTitle').textContent   = title;
        document.getElementById('confirmModalMessage').textContent = message;
        document.getElementById('confirmModalIcon').textContent    = icon;
        document.getElementById('confirmModalOk').textContent      = okText;
        document.getElementById('confirmModalOk').className        = okType;

        const backdrop = document.getElementById('confirmModalBackdrop');
        backdrop.classList.add('show');

        return new Promise(resolve => {
            confirmModalResolve = (result) => {
                backdrop.classList.remove('show');
                resolve(result);
            };
        });
    }

    // Close on backdrop click
    document.getElementById('confirmModalBackdrop').addEventListener('click', function(e) {
        if (e.target === this) confirmModalResolve(false);
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('confirmModalBackdrop').classList.contains('show')) {
            confirmModalResolve(false);
        }
    });
    // ── Logout confirmation ──
    async function handleLogout() {
        const confirmed = await showConfirm({
            title:   'Log Out',
            message: 'Are you sure you want to log out of your account?',
            okText:  'Log Out',
            okType:  'primary',
            icon:    '👋'
        });
        if (confirmed) {
            document.getElementById('logoutForm').submit();
        }
    }

    // Global handler for all data-confirm forms (delete buttons)
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('button[type="submit"], button:not([type])');
        if (!btn) return;
        const form = btn.closest('form[data-confirm-title]');
        if (!form) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const confirmed = await showConfirm({
            title:   form.dataset.confirmTitle   || 'Are you sure?',
            message: form.dataset.confirmMessage || 'This action cannot be undone.',
            okText:  form.dataset.confirmOk      || 'Confirm',
            okType:  form.dataset.confirmType    || 'danger',
            icon:    form.dataset.confirmIcon    || '⚠️',
        });
        if (confirmed) form.submit();
    });
    </script>
</body>
</html>
