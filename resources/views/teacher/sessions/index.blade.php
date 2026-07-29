@extends('layouts.app')
@section('title', 'My Sessions')
@section('page-title', 'Class Sessions')

@section('content')
{{-- Start New Session --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-play-circle-fill text-success me-2"></i>Start a New Class Session</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.sessions.start') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject') }}" placeholder="e.g. Mathematics">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <input type="text" name="section" class="form-control @error('section') is-invalid @enderror"
                       value="{{ old('section') }}" placeholder="e.g. Grade 7 — A">
                @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Camera</label>
                <select name="camera_id" class="form-select @error('camera_id') is-invalid @enderror">
                    <option value="">— Select Camera —</option>
                    @foreach($cameras as $camera)
                        <option value="{{ $camera->id }}">{{ $camera->name }} ({{ $camera->location }})</option>
                    @endforeach
                </select>
                @error('camera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-play-fill me-1"></i> Start
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Session History --}}
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Session History</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Subject</th><th>Section</th><th>Camera</th><th>Started</th><th>Ended</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->subject }}</td>
                        <td>{{ $session->section }}</td>
                        <td>{{ $session->camera->location }}</td>
                        <td>{{ $session->started_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        <td>{{ $session->ended_at?->format('h:i A') ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $session->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('teacher.sessions.live', $session) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye-fill"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $sessions->links() }}
@endsection
