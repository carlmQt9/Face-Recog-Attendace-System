@extends('layouts.app')
@section('title', 'Live Roster')
@section('page-title', 'Live Class Roster — ' . $session->subject)

@section('content')
<div class="row g-4">

    {{-- Live Roster --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        {{ $session->subject }} — {{ $session->section }}
                    </h6>
                    <small class="text-muted">
                        📍 {{ $session->camera->location }}
                        &nbsp;|&nbsp; Started: {{ $session->started_at?->format('h:i A') }}
                        &nbsp;|&nbsp;
                        @if($session->session_type === 'afternoon_out')
                            <span class="badge" style="background:#fee2e2;color:#991b1b;">🌇 Afternoon — Time Out</span>
                        @else
                            <span class="badge" style="background:#dcfce7;color:#15803d;">🌅 Morning — Time In</span>
                        @endif
                    </small>
                </div>
                @if($session->isActive())
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('teacher.sessions.camera', $session) }}"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-camera-video-fill me-1"></i> Open Camera
                        </a>
                        <form action="{{ route('teacher.sessions.stop', $session) }}" method="POST">
                            @csrf
                            <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('End this session?')">
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
                            <th>Student</th>
                            <th>Method</th>
                            <th>
                                <span class="text-success">Time In</span>
                            </th>
                            <th>
                                <span class="text-danger">Time Out</span>
                            </th>
                            <th>Duration</th>
                            <th>Notified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendance as $i => $record)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                <strong>{{ $record->student->user->name }}</strong>
                            </td>
                            <td>
                                @php
                                    $methodMap = ['face_scan'=>['bg-primary','Face'],
                                                  'manual'   =>['bg-warning text-dark','Manual'],
                                                  'qr_code'  =>['bg-info text-dark','QR']];
                                    [$cls,$lbl] = $methodMap[$record->method] ?? ['bg-secondary','—'];
                                @endphp
                                <span class="badge {{ $cls }}">{{ $lbl }}</span>
                            </td>
                            {{-- Time In --}}
                            <td>
                                <span class="badge bg-success">
                                    {{ $record->arrived_at->format('h:i A') }}
                                </span>
                            </td>
                            {{-- Time Out --}}
                            <td>
                                @if($record->time_out)
                                    <span class="badge bg-danger">
                                        {{ $record->time_out->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            {{-- Duration --}}
                            <td>
                                <span class="text-muted small">{{ $record->durationLabel() }}</span>
                            </td>
                            {{-- Notifications --}}
                            <td>
                                <div class="d-flex gap-1 align-items-center">
                                    {{-- Time-in notification --}}
                                    @if($record->notification_sent)
                                        <i class="bi bi-envelope-check-fill text-success"
                                           title="Time-in email sent"></i>
                                    @else
                                        <i class="bi bi-envelope text-secondary"
                                           title="Time-in email not sent"></i>
                                    @endif
                                    {{-- Time-out notification --}}
                                    @if($record->time_out)
                                        @if($record->time_out_notification_sent)
                                            <i class="bi bi-envelope-check-fill text-primary"
                                               title="Time-out email sent"></i>
                                        @else
                                            <i class="bi bi-envelope-dash text-warning"
                                               title="Time-out email pending"></i>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center text-muted py-4">
                                Waiting for students to scan in…
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Manual Override --}}
    @if($session->isActive())
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-hand-index-fill text-warning me-2"></i>Manual Mark
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Use for face/QR issues. Select student and scan type.
                </p>
                <form action="{{ route('teacher.sessions.manual-attend', $session) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" id="studentSearch" class="form-control form-control-sm mb-2"
                               placeholder="Filter by name…" oninput="filterStudents()">
                        <select name="student_id" id="studentSelect" class="form-select form-select-sm" size="5">
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Scan Type</label>
                        <div class="d-flex gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scan_type"
                                       id="manualIn" value="time_in"
                                       {{ $session->session_type !== 'afternoon_out' ? 'checked' : '' }}>
                                <label class="form-check-label text-success fw-semibold" for="manualIn">
                                    Time In
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scan_type"
                                       id="manualOut" value="time_out"
                                       {{ $session->session_type === 'afternoon_out' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold" for="manualOut">
                                    Time Out
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 btn-sm">
                        <i class="bi bi-person-check-fill me-1"></i> Mark
                    </button>
                </form>
            </div>
        </div>

        {{-- Legend --}}
        <div class="card">
            <div class="card-body py-3">
                <p class="fw-semibold mb-2 small">Parent Notifications</p>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-envelope-check-fill text-success"></i>
                    <span class="small">Time-in email sent</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-envelope-check-fill text-primary"></i>
                    <span class="small">Time-out email sent</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-dash text-warning"></i>
                    <span class="small">Time-out email pending</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function filterStudents() {
    const q   = document.getElementById('studentSearch').value.toLowerCase();
    const sel = document.getElementById('studentSelect');
    Array.from(sel.options).forEach(o => {
        o.style.display = o.text.toLowerCase().includes(q) ? '' : 'none';
    });
}

@if($session->isActive())
setTimeout(() => location.reload(), 8000);
@endif
</script>
@endpush
