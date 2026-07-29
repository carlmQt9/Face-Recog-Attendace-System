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
                    <a href="{{ route('teacher.sessions.index') }}" class="nav-link {{ request()->is('teacher*') ? 'active' : '' }}">
                        <i class="bi bi-camera-video-fill"></i> My Sessions
                    </a>

                @elseif(auth()->user()->isStudent())
                    <a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->is('student*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i> My Attendance
                    </a>
                @endif

                <hr style="border-color:rgba(255,255,255,0.1);">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
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

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
