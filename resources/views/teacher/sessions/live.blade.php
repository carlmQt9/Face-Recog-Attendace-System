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
                        @if($session->isActive())
                            <span id="livePill" class="ms-2 badge bg-success" style="font-size:10px;vertical-align:middle;">● Live</span>
                        @endif
                    </h6>
                    <small class="text-muted">
                        📍 {{ $session->camera->location }}
                        &nbsp;|&nbsp; Started: {{ $session->started_at?->format('h:i A') }}
                        &nbsp;|&nbsp; <span id="rosterCount">{{ $attendance->count() }}</span> scanned
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
                        <form action="{{ route('teacher.sessions.stop', $session) }}" method="POST" id="endSessionForm">
                            @csrf
                            <button class="btn btn-danger btn-sm"
                                    type="button"
                                    onclick="handleEndSessionLive()">
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
                            <th><span class="text-success">Time In</span></th>
                            <th><span class="text-danger">Time Out</span></th>
                            <th>Duration</th>
                            <th>Notified</th>
                        </tr>
                    </thead>
                    <tbody id="rosterTableBody">
                        @forelse($attendance as $i => $record)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $thumbUrl = $record->snapshotUrl()
                                            ?? ($record->student->face_encoding && Storage::disk('public')->exists($record->student->face_encoding)
                                                ? Storage::url($record->student->face_encoding)
                                                : null);
                                        $thumbCaption = $record->student->user->name;
                                        $thumbSub = ($record->scan_type === 'time_out' ? 'Timed Out' : 'Timed In') . ' · ' . $record->arrived_at->format('h:i A');
                                    @endphp
                                    @if($thumbUrl)
                                        <img src="{{ $thumbUrl }}"
                                             alt="{{ $thumbCaption }}"
                                             data-lightbox="{{ $thumbUrl }}"
                                             data-lightbox-caption="{{ $thumbCaption }}"
                                             data-lightbox-sub="{{ $thumbSub }}"
                                             style="width:36px;height:36px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid #dee2e6;">
                                    @else
                                        <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="bi bi-person-fill text-white" style="font-size:16px;"></i>
                                        </div>
                                    @endif
                                    <strong>{{ $record->student->user->name }}</strong>
                                </div>
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
// Smart real-time refresh — only reloads if attendance count changed
let lastCount = {{ $attendance->count() }};

async function pollLiveRoster() {
    try {
        const resp = await fetch(window.location.href, {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!resp.ok) return;
        const html    = await resp.text();
        const parser  = new DOMParser();
        const doc     = parser.parseFromString(html, 'text/html');
        const newBody = doc.getElementById('rosterTableBody');
        const newCount = parseInt(doc.getElementById('rosterCount')?.textContent ?? lastCount);

        if (newCount !== lastCount) {
            lastCount = newCount;
            const curBody = document.getElementById('rosterTableBody');
            if (curBody && newBody) curBody.innerHTML = newBody.innerHTML;
            const curCount = document.getElementById('rosterCount');
            if (curCount) curCount.textContent = newCount;

            // Flash live indicator
            const pill = document.getElementById('livePill');
            if (pill) {
                pill.classList.replace('bg-success', 'bg-warning');
                pill.textContent = '● Updated';
                setTimeout(() => {
                    pill.classList.replace('bg-warning', 'bg-success');
                    pill.textContent = '● Live';
                }, 1200);
            }
        }
    } catch(e) { console.warn('Live roster poll failed:', e); }
}

setInterval(pollLiveRoster, 8000);
@endif

async function handleEndSessionLive() {
    const confirmed = await showConfirm({
        title:   'End Class Session',
        message: 'Are you sure you want to end this session for {{ $session->subject }} ({{ $session->section }})? No more attendance will be recorded.',
        okText:  'End Session',
        okType:  'danger',
        icon:    '🛑'
    });
    if (confirmed) {
        document.getElementById('endSessionForm').submit();
    }
}
</script>
@endpush
