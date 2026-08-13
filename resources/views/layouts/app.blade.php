<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DMCMES Attendance') | Smart Attendance System</title>
    <link rel="icon" type="image/png" href="{{ asset('dmcmes-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ── DMCMES Theme Colors ──
           Primary  : #0c3d8a  (deep navy blue)
           Accent   : #f5a800  (gold/yellow)
           Secondary: #1a6b3c  (forest green)
           Light bg : #f0f4f8
        ── */

        body { background-color: #f0f4f8; }

        /* ── Sidebar ── */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0c2d6b 0%, #0a2455 100%);
            color: #fff;
            width: 240px; position: fixed; top: 0; left: 0; z-index: 1050;
            display: flex; flex-direction: column;
            transition: transform 0.3s ease;
            border-right: 3px solid #f5a800;
        }
        .sidebar .brand {
            padding: 14px 16px; font-size: 14px; font-weight: bold;
            border-bottom: 1px solid rgba(245,168,0,0.3);
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(0,0,0,0.2);
        }
        .sidebar .brand-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        .sidebar .brand-logo img {
            width: 38px; height: 38px; object-fit: contain; border-radius: 6px;
        }
        .sidebar .brand-text { line-height: 1.2; }
        .sidebar .brand-text .school-abbr {
            font-size: 16px; font-weight: 900; color: #f5a800; letter-spacing:.04em;
        }
        .sidebar .brand-text .school-full {
            font-size: 9px; color: rgba(255,255,255,0.6); font-weight: 500;
            text-transform: uppercase; letter-spacing: .05em; display: block;
        }
        .sidebar .brand-close {
            display: none; background: none; border: none;
            color: rgba(255,255,255,0.7); font-size: 20px; cursor: pointer;
            padding: 0 4px; line-height: 1;
        }
        .sidebar nav { padding: 8px; flex: 1; overflow-y: auto; }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75); padding: 10px 16px;
            display: flex; align-items: center; gap: 10px;
            border-radius: 6px; font-size: 14px;
        }
        .sidebar .nav-link:hover {
            color: #f5a800; background: rgba(245,168,0,0.1);
        }
        .sidebar .nav-link.active {
            color: #fff; background: rgba(245,168,0,0.2);
            border-left: 3px solid #f5a800; padding-left: 13px;
        }

        /* ── Sidebar backdrop ── */
        .sidebar-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.55); z-index: 1040;
        }
        .sidebar-backdrop.show { display: block; }

        /* ── Hamburger button ── */
        .hamburger-btn {
            display: none; background: none; border: none;
            font-size: 24px; color: #0c3d8a; cursor: pointer;
            padding: 4px 8px; border-radius: 8px; line-height: 1; flex-shrink: 0;
        }
        .hamburger-btn:hover { background: #e8f0fe; }

        /* ── Main content ── */
        .main-content { margin-left: 240px; padding: 24px; min-width: 0; }
        .top-bar {
            background: #fff; padding: 12px 16px;
            margin: -24px -24px 24px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2px solid #f5a800; flex-wrap: wrap; gap: 8px;
        }

        /* ── Cards / badges ── */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .stat-card { border-radius: 12px; padding: 20px; color: #fff; }
        .badge-role { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }

        /* ── Override Bootstrap primary color to DMCMES navy ── */
        .btn-primary { background-color: #0c3d8a; border-color: #0c3d8a; }
        .btn-primary:hover { background-color: #0a3070; border-color: #0a3070; }
        .btn-primary:focus, .btn-primary:active { background-color: #0a3070 !important; border-color: #0a3070 !important; box-shadow: 0 0 0 .25rem rgba(12,61,138,.35) !important; }

        .badge.bg-primary { background-color: #0c3d8a !important; }
        .btn-outline-primary { color: #0c3d8a; border-color: #0c3d8a; }
        .btn-outline-primary:hover { background-color: #0c3d8a; border-color: #0c3d8a; color: #fff; }

        .pagination .page-item.active .page-link { background: #0c3d8a; border-color: #0c3d8a; }

        /* ── Pagination ── */
        .pagination { flex-wrap: wrap; gap: 4px; }
        .pagination .page-link {
            border-radius: 8px !important; border-color: #e2e8f0;
            color: #475569; font-size: 13px; padding: 6px 12px;
        }
        .pagination .page-item.disabled .page-link { color: #cbd5e1; }
        .pagination .page-item.disabled .page-link { color: #cbd5e1; }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar .brand-close { display: inline-flex; align-items: center; }
            .main-content { margin-left: 0; padding: 16px 12px; }
            .top-bar { margin: -16px -12px 16px; padding: 10px 14px; }
            .hamburger-btn { display: inline-flex; align-items: center; }
        }

        /* ── Bootstrap modal — mobile bottom sheet ── */
        @media (max-width: 575.98px) {
            /* Anchor the modal to the bottom of the screen */
            .modal-dialog {
                margin: 0 auto !important;
                max-width: 100% !important;
                width: 100% !important;
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                top: auto !important;
            }
            .modal-content {
                border-radius: 20px 20px 0 0 !important;
                max-height: 88dvh !important;
                overflow-y: auto;
                border: none !important;
            }
            /* Slide-up animation */
            .modal.fade .modal-dialog {
                transform: translateY(100%) !important;
                transition: transform .3s ease-out !important;
            }
            .modal.show .modal-dialog {
                transform: translateY(0) !important;
            }
            .modal-header { padding: 14px 16px !important; }
            .modal-body   { padding: 16px !important; overflow-y: auto; max-height: calc(88dvh - 120px); }
            .modal-footer {
                padding: 10px 16px calc(10px + env(safe-area-inset-bottom)) !important;
                flex-wrap: wrap;
                gap: 8px;
            }
            .modal-footer .btn { flex: 1 0 45%; min-width: 0; max-width: calc(50% - 4px); font-size: 13px; padding: 8px 6px; }
            
            /* Optimize form spacing in modals for mobile */
            .modal-body .form-label { font-size: 13px; margin-bottom: 6px; }
            .modal-body .form-control,
            .modal-body .form-select { font-size: 14px; padding: 9px 12px; }
            .modal-body .mb-3 { margin-bottom: 12px !important; }
            .modal-body .row.g-2 > div { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        }
        
        /* ── Desktop modal sizing adjustments for better UX ── */
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 540px !important;
            }
        }

        /* ── Mobile modal form field optimization ── */
        @media (max-width: 575.98px) {
            /* Ensure form fields are full width and don't overflow */
            .modal-body .form-control,
            .modal-body .form-select { 
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            /* Make sure row/col layout doesn't break */
            .modal-body .row {
                margin-left: -8px !important;
                margin-right: -8px !important;
            }
            .modal-body .row > [class*='col-'] {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
            /* Reduce modal footer buttons to fit side by side */
            .modal-footer .btn-group,
            .modal-footer .d-flex {
                width: 100%;
                flex-wrap: wrap;
                gap: 8px;
            }
            /* Ensure buttons don't shrink too much */
            .modal-footer .btn {
                min-width: 100px;
                font-size: 13px;
                padding: 8px 12px;
            }
            /* For single buttons, let them be full width */
            .modal-footer .btn:only-child {
                min-width: auto;
            }
        }

        /* ── Mobile card-list rows (tables that switch to card layout) ── */
        @media (max-width: 575.98px) {
            .table-card-mobile thead { display: none; }
            .table-card-mobile,
            .table-card-mobile tbody,
            .table-card-mobile tr { display: block; width: 100%; }
            .table-card-mobile tr {
                border-bottom: 1px solid #f1f5f9;
                padding: 10px 12px;
                display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            }
            .table-card-mobile td { border: none !important; padding: 0 !important; font-size: 13px; }
            .table-card-mobile td.td-avatar  { flex-shrink: 0; }
            .table-card-mobile td.td-main    { flex: 1; min-width: 0; }
            .table-card-mobile td.td-badge   { flex-shrink: 0; }
            /* actions row — buttons are auto width, NOT stretched */
            .table-card-mobile td.td-actions {
                width: 100%; display: flex; gap: 6px; margin-top: 4px; flex-wrap: wrap;
            }
            .table-card-mobile td.td-actions .btn {
                flex: 0 0 auto;
                font-size: 12px; padding: 5px 10px;
            }
            .table-card-mobile td.td-hide { display: none; }
        }

        /* ── Mobile-only flex list rows ── */
        /* .mob-list  = visible only on mobile */
        /* .desk-list = visible only on desktop */
        .mob-list  { display: none; }
        .desk-list { display: block; }
        @media (max-width: 575.98px) {
            .mob-list  { display: block; }
            .desk-list { display: none !important; }
        }

        /* Item row used in mob-list sections */
        .item-row {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 14px; border-bottom: 1px solid #f1f5f9; min-width: 0;
        }
        .item-row:last-child { border-bottom: none; }
        .item-row .ir-icon    { flex-shrink: 0; }
        .item-row .ir-info    { flex: 1; min-width: 0; }
        .item-row .ir-name    { font-weight: 600; font-size: 14px; line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .item-row .ir-sub     { font-size: 11px; color: #64748b; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis; }
        .item-row .ir-badges  { flex-shrink: 0; display: flex; gap: 4px; flex-wrap: wrap; }
        .item-row .ir-actions { flex-shrink: 0; display: flex; gap: 5px; align-items: center; }
    </style>
    @stack('styles')
</head>
<body>

    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="brand">
            <a href="#" class="brand-logo">
                <img src="<?php echo 'data:'.mime_content_type(public_path('dmcmes-logo.png')).';base64,'.base64_encode(file_get_contents(public_path('dmcmes-logo.png'))); ?>" alt="DMCMES Logo" style="width:40px;height:40px;object-fit:contain;">
                <div class="brand-text">
                    <span class="school-abbr">DMCMES</span>
                    <span class="school-full">Don Marcelo C. Marty ES</span>
                </div>
            </a>
            <button class="brand-close" onclick="closeSidebar()" title="Close menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <nav class="mt-2">
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
                <hr style="border-color:rgba(255,255,255,0.1);margin:8px 0;">
                <form action="{{ route('logout') }}" method="POST" id="logoutForm" style="display:none;">
                    @csrf
                </form>
                <button type="button" class="nav-link border-0 bg-transparent w-100 text-start" onclick="handleLogout()" id="logoutBtn">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            @endauth
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex align-items-center gap-2">
                <button class="hamburger-btn" onclick="openSidebar()" title="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0" style="font-size:clamp(14px,4vw,18px);">@yield('page-title', 'Dashboard')</h5>
            </div>
            @auth
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge badge-role" style="background:#0c3d8a;color:#f5a800;border:1px solid #f5a800;">{{ strtoupper(auth()->user()->role) }}</span>
                    <span class="text-muted small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </div>
            @endauth
        </div>

        @foreach(['success','warning','danger','info'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }} alert-dismissible fade show auto-dismiss" role="alert">
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarBackdrop').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarBackdrop').classList.remove('show');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); });
    });

    // Auto-dismiss notifications after 2 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert.auto-dismiss');
        alerts.forEach(function(alert) {
            // Set a timeout to auto-dismiss the alert
            setTimeout(function() {
                // Use Bootstrap's dismiss method if available
                const alertInstance = bootstrap.Alert.getOrCreateInstance(alert);
                if (alertInstance) {
                    alertInstance.close();
                } else {
                    // Fallback: manually remove the alert with fade effect
                    alert.classList.remove('show');
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 150); // Wait for CSS transition
                }
            }, 2000); // 2 seconds delay
        });
    });

    // Also handle dynamically added notifications
    function observeForNewAlerts() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('alert') && node.classList.contains('auto-dismiss')) {
                        setTimeout(function() {
                            const alertInstance = bootstrap.Alert.getOrCreateInstance(node);
                            if (alertInstance) {
                                alertInstance.close();
                            } else {
                                node.classList.remove('show');
                                setTimeout(() => {
                                    if (node.parentNode) {
                                        node.parentNode.removeChild(node);
                                    }
                                }, 150);
                            }
                        }, 2000);
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Start observing after DOM is loaded
    document.addEventListener('DOMContentLoaded', observeForNewAlerts);

    // Global utility function to show auto-dismissing notifications
    window.showNotification = function(message, type = 'success', duration = 2000) {
        // Create the alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show auto-dismiss d-flex align-items-center gap-2 mb-3`;
        alertDiv.setAttribute('role', 'alert');
        
        // Add appropriate icon based on type
        const icons = {
            success: 'bi-check-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            danger: 'bi-x-circle-fill',
            info: 'bi-info-circle-fill',
            error: 'bi-x-circle-fill'
        };
        
        const icon = icons[type] || icons.success;
        
        alertDiv.innerHTML = `
            <i class="bi ${icon}"></i> ${message}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert the alert at the top of the main content, after the top bar
        const mainContent = document.querySelector('.main-content');
        const topBar = mainContent.querySelector('.top-bar');
        if (topBar) {
            topBar.insertAdjacentElement('afterend', alertDiv);
        } else {
            mainContent.insertBefore(alertDiv, mainContent.firstChild);
        }
        
        // Auto-dismiss after the specified duration
        setTimeout(function() {
            const alertInstance = bootstrap.Alert.getOrCreateInstance(alertDiv);
            if (alertInstance) {
                alertInstance.close();
            } else {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 150);
            }
        }, duration);
        
        return alertDiv;
    };

    // Also provide a shorthand for common notification types
    window.showSuccess = (message, duration) => showNotification(message, 'success', duration);
    window.showError = (message, duration) => showNotification(message, 'danger', duration);
    window.showWarning = (message, duration) => showNotification(message, 'warning', duration);
    window.showInfo = (message, duration) => showNotification(message, 'info', duration);
    </script>

    @stack('scripts')

    <!-- ══ LOGOUT HANDLER ══ -->
    <script>
    async function handleLogout(){
        const c=await showConfirm({
            title:'Log Out',
            message:'Are you sure you want to log out?',
            okText:'Log Out',
            okType:'primary',
            icon:'👋'
        });
        if(c) {
            try {
                const form = document.getElementById('logoutForm');
                if(form) {
                    // Use AJAX to ensure proper logout even if form submission fails
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': token
                        },
                        body: 'logout=true',
                        credentials: 'include'
                    });
                    if (response.ok) {
                        window.location.href = '{{ route('landing') }}';
                    } else {
                        throw new Error('Logout failed');
                    }
                } else {
                    throw new Error('Form not found');
                }
            } catch(error) {
                console.error('Logout error:', error);
                // Force redirect as fallback
                window.location.href = '{{ route('logout') }}';
            }
        }
    }
    
    // Ensure logout form is available
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('logoutForm')) {
            console.warn('Logout form not found in DOM - it may not render properly');
        }
    });
    </script>

    <!-- ══ LIGHTBOX ══ -->
    <style>
        #imgLightbox { position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.88);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;opacity:0;pointer-events:none;transition:opacity .22s ease;cursor:zoom-out; }
        #imgLightbox.show { opacity:1;pointer-events:all; }
        #imgLightboxImg { max-width:min(90vw,520px);max-height:75vh;border-radius:18px;box-shadow:0 32px 80px rgba(0,0,0,.7);object-fit:contain;transform:scale(0.85);transition:transform .28s cubic-bezier(.34,1.56,.64,1);display:block; }
        #imgLightbox.show #imgLightboxImg { transform:scale(1); }
        #imgLightboxCaption { color:#e2e8f0;font-size:15px;font-weight:700;text-align:center; }
        #imgLightboxSub { color:#64748b;font-size:13px;text-align:center;margin-top:-8px; }
        #imgLightboxClose { position:fixed;top:18px;right:20px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.12);border:none;color:#fff;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center; }
        [data-lightbox] { cursor:zoom-in !important;transition:transform .18s,box-shadow .18s; }
        [data-lightbox]:hover { transform:scale(1.07) !important;box-shadow:0 0 0 3px rgba(99,102,241,.6) !important; }
    </style>
    <div id="imgLightbox" onclick="closeLightbox()">
        <button id="imgLightboxClose" onclick="closeLightbox()">✕</button>
        <img id="imgLightboxImg" src="" alt="">
        <div id="imgLightboxCaption"></div>
        <div id="imgLightboxSub"></div>
    </div>
    <script>
    function openLightbox(src,caption,sub){document.getElementById('imgLightboxImg').src=src;document.getElementById('imgLightboxCaption').textContent=caption||'';document.getElementById('imgLightboxSub').textContent=sub||'';document.getElementById('imgLightbox').classList.add('show');document.body.style.overflow='hidden';}
    function closeLightbox(){document.getElementById('imgLightbox').classList.remove('show');document.body.style.overflow='';setTimeout(()=>{document.getElementById('imgLightboxImg').src='';},250);}
    document.addEventListener('keydown',e=>{if(e.key==='Escape'&&document.getElementById('imgLightbox').classList.contains('show'))closeLightbox();});
    document.addEventListener('click',e=>{const el=e.target.closest('[data-lightbox]');if(!el)return;e.stopPropagation();openLightbox(el.dataset.lightbox,el.dataset.lightboxCaption||'',el.dataset.lightboxSub||'');});
    </script>

    <!-- ══ CONFIRM MODAL ══ -->
    <style>
        #confirmModalBackdrop { position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;pointer-events:none;transition:opacity .22s ease;overflow-y:auto; }
        #confirmModalBackdrop.show { opacity:1;pointer-events:all; }
        #confirmModalBox { background:#fff;border-radius:18px;padding:28px 20px 20px;min-width:min(300px,100%);max-width:420px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.25);transform:scale(.88) translateY(16px);transition:transform .25s cubic-bezier(.34,1.56,.64,1),opacity .22s ease;opacity:0;max-height:calc(100dvh - 32px);overflow-y:auto; }
        #confirmModalBackdrop.show #confirmModalBox { transform:scale(1) translateY(0);opacity:1; }
        #confirmModalIcon { font-size:38px;margin-bottom:12px;display:block;text-align:center; }
        #confirmModalTitle { font-size:17px;font-weight:800;color:#0f172a;text-align:center;margin-bottom:8px; }
        #confirmModalMessage { font-size:14px;color:#64748b;text-align:center;line-height:1.6;margin-bottom:24px; }
        .confirm-modal-actions { display:flex;gap:10px;justify-content:center; }
        #confirmModalCancel { flex:1;padding:10px 0;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:14px;font-weight:600;cursor:pointer; }
        #confirmModalOk { flex:1;padding:10px 0;border-radius:10px;border:none;font-size:14px;font-weight:700;cursor:pointer; }
        #confirmModalOk.danger  { background:linear-gradient(135deg,#dc2626,#f87171);color:#fff; }
        #confirmModalOk.warning { background:linear-gradient(135deg,#d97706,#fbbf24);color:#fff; }
        #confirmModalOk.primary { background:linear-gradient(135deg,#0c3d8a,#1a6b3c);color:#fff; }
        #confirmModalOk.success { background:linear-gradient(135deg,#059669,#34d399);color:#fff; }
    </style>
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
    let confirmModalResolve=null;
    function showConfirm({title='Are you sure?',message='This action cannot be undone.',okText='Confirm',okType='danger',icon='⚠️'}={}){
        document.getElementById('confirmModalTitle').textContent=title;
        document.getElementById('confirmModalMessage').textContent=message;
        document.getElementById('confirmModalIcon').textContent=icon;
        document.getElementById('confirmModalOk').textContent=okText;
        document.getElementById('confirmModalOk').className=okType;
        document.getElementById('confirmModalBackdrop').classList.add('show');
        return new Promise(resolve=>{confirmModalResolve=(result)=>{document.getElementById('confirmModalBackdrop').classList.remove('show');resolve(result);};});
    }
    document.getElementById('confirmModalBackdrop').addEventListener('click',function(e){if(e.target===this)confirmModalResolve(false);});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'&&document.getElementById('confirmModalBackdrop').classList.contains('show'))confirmModalResolve(false);});
    document.addEventListener('click',async function(e){const btn=e.target.closest('button[type="submit"],button:not([type])');if(!btn)return;const form=btn.closest('form[data-confirm-title]');if(!form)return;e.preventDefault();e.stopImmediatePropagation();const c=await showConfirm({title:form.dataset.confirmTitle||'Are you sure?',message:form.dataset.confirmMessage||'This cannot be undone.',okText:form.dataset.confirmOk||'Confirm',okType:form.dataset.confirmType||'danger',icon:form.dataset.confirmIcon||'⚠️'});if(c)form.submit();});

    // ── Global modal form validation ──────────────────────────────────────────
    // Intercepts submit-button clicks for forms that have data-validate="true"
    // Skips fields inside .d-none containers (hidden role-conditional sections)
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('button[type="submit"][form]');
        if (!btn) return;
        const formId = btn.getAttribute('form');
        if (!formId) return;
        const form = document.getElementById(formId);
        if (!form || !form.dataset.validate) return;

        const missing = [];
        form.querySelectorAll('[data-label]').forEach(el => {
            // Skip fields inside hidden containers
            if (el.closest('.d-none')) return;
            const val = el.value?.trim();
            if (!val) {
                el.classList.add('is-invalid');
                missing.push(el.dataset.label);
            } else {
                el.classList.remove('is-invalid');
            }
        });

        if (missing.length) {
            e.preventDefault();
            e.stopImmediatePropagation();
            await showConfirm({
                title:   'Missing Required Fields',
                message: `Please fill in: ${missing.join(', ')}.`,
                okText:  'Got it',
                okType:  'warning',
                icon:    '⚠️',
            });
        }
    });

    // Remove invalid highlight when user fixes a field
    document.addEventListener('input', function(e) {
        if (e.target.dataset.label) e.target.classList.remove('is-invalid');
    });
    document.addEventListener('change', function(e) {
        if (e.target.dataset.label) e.target.classList.remove('is-invalid');
    });
    </script>
</body>
</html>
