@extends('layouts.app')
@section('title', 'Live Roster')
@section('page-title', 'Live Class Roster — ' . $session->subject)

@section('content')
<div class="row g-4">
    {{-- Live Roster --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        {{ $session->subject }} — {{ $session->section }}
                    </h6>
                    <small class="text-muted">
                        📍 {{ $session->camera->location }} &nbsp;|&nbsp;
                        Started: {{ $session->started_at?->format('h:i A') }}
                    </small>
                </div>
                @if($session->isActive())
                    <div class="d-flex gap-2">
                        <a href="{{ route('teacher.sessions.camera', $session) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-camera-video-fill me-1"></i> Open Camera
                        </a>
                        <form action="{{ route('teacher.sessions.stop', $session) }}" method="POST">
                            @csrf
                            <button class="btn btn-danger btn-sm" onclick="return confirm('End this session?')">
                                <i class="bi bi-stop-circle-fill me-1"></i> End Session
                            </button>
                        </form>
                    </div>
                @else
                    <span class="badge bg-secondary fs-6">Session Ended</span>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="rosterTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Method</th>
                            <th>Time</th>
                            <th>Notified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendance as $i => $record)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    {{ $record->student->user->name }}
                                </td>
                                <td>
                                    <span class="badge {{ $record->method === 'manual' ? 'bg-warning text-dark' : 'bg-success' }}">
                                        {{ $record->method === 'manual' ? 'Manual' : 'Face Scan' }}
                                    </span>
                                </td>
                                <td>{{ $record->arrived_at->format('h:i A') }}</td>
                                <td>
                                    @if($record->notification_sent)
                                        <i class="bi bi-envelope-check-fill text-success"></i>
                                    @else
                                        <i class="bi bi-dash text-secondary"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow"><td colspan="5" class="text-center text-muted py-4">Waiting for students to scan in…</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Manual Override --}}
    @if($session->isActive())
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-hand-index-fill text-warning me-2"></i>Manual Present</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Use this for face injury, heavy glasses, or poor lighting.</p>
                <form action="{{ route('teacher.sessions.manual-attend', $session) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Search & Select Student</label>
                        <input type="text" id="studentSearch" class="form-control mb-2" placeholder="Type name to filter…" oninput="filterStudents()">
                        <select name="student_id" id="studentSelect" class="form-select" size="6">
                            @foreach($session->camera->attendanceRecords->pluck('student_id')->toArray() ?? [] as $sid)
                            @endforeach
                        </select>
                        <div id="studentListContainer">
                            {{-- populated by JS from session context --}}
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-person-check-fill me-1"></i> Mark Present
                    </button>
                </form>
            </div>
        </div>

        {{-- Sound indicator --}}
        <div class="card mt-3">
            <div class="card-body text-center">
                <p class="mb-2 fw-semibold">Audio Signals</p>
                <p class="mb-1"><span class="text-success fs-5">🔊</span> 1 Beep = Success</p>
                <p class="mb-0"><span class="text-danger fs-5">🔊🔊</span> 2 Beeps = Error</p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Audio elements --}}
<audio id="successBeep" src="/sounds/success.mp3" preload="auto"></audio>
<audio id="errorBeep"   src="/sounds/error.mp3"   preload="auto"></audio>
@endsection

@push('scripts')
<script>
// Auto-refresh roster every 5 seconds when session is active
@if($session->isActive())
setTimeout(() => location.reload(), 5000);
@endif
</script>
@endpush
