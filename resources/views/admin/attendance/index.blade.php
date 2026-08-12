@extends('layouts.app')
@section('title', 'Attendance Archive')
@section('page-title', 'Attendance Archive')

@section('content')

{{-- Filter Bar --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select form-select-sm">
                    <option value="">All Students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Camera</label>
                <select name="camera_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($cameras as $c)
                        <option value="{{ $c->id }}" {{ request('camera_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Type</label>
                <select name="scan_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="time_in"  {{ request('scan_type') === 'time_in'  ? 'selected' : '' }}>Time-In</option>
                    <option value="time_out" {{ request('scan_type') === 'time_out' ? 'selected' : '' }}>Time-Out</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <a href="{{ route('admin.attendance.export', request()->all()) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-download me-1"></i>CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 table-card-mobile" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th class="d-none d-md-table-cell">ID</th>
                    <th class="d-none d-md-table-cell">Location</th>
                    <th class="d-none d-sm-table-cell">Method</th>
                    <th>Time In</th>
                    <th class="d-none d-sm-table-cell text-danger">Time Out</th>
                    <th class="d-none d-lg-table-cell">Duration</th>
                    <th class="d-none d-lg-table-cell">Notified</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td class="td-main">
                        @php
                            $thumbUrl = $record->snapshotUrl(); // Only use attendance snapshot, not registration photo
                                    : null);
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            @if($thumbUrl)
                                <img src="{{ $thumbUrl }}"
                                     alt="{{ $record->student->user->name }}"
                                     data-lightbox="{{ $thumbUrl }}"
                                     data-lightbox-caption="{{ $record->student->user->name }}"
                                     data-lightbox-sub="{{ $record->student->student_id }}"
                                     style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid #dee2e6;">
                            @else
                                <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#0c3d8a,#1a6b3c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-person-fill text-white" style="font-size:15px;"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold" style="line-height:1.2;">{{ $record->student->user->name }}</div>
                                <div class="text-muted d-md-none" style="font-size:11px;">{{ $record->camera->location }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-hide text-muted d-none d-md-table-cell">{{ $record->student->student_id }}</td>
                    <td class="td-hide d-none d-md-table-cell">{{ $record->camera->location }}</td>
                    <td class="td-hide d-none d-sm-table-cell">
                        @php
                            $mmap = ['face_scan'=>'bg-primary','manual'=>'bg-warning text-dark','qr_code'=>'bg-info text-dark'];
                            $mcls = $mmap[$record->method] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $mcls }}">
                            {{ ucfirst(str_replace('_',' ',$record->method)) }}
                        </span>
                    </td>
                    {{-- Time In --}}
                    <td class="td-badge">
                        <span class="badge bg-success" style="white-space:nowrap;">{{ $record->arrived_at->format('h:i A') }}</span>
                        <div class="text-muted" style="font-size:11px;white-space:nowrap;">{{ $record->arrived_at->format('M d, Y') }}</div>
                    </td>
                    {{-- Time Out --}}
                    <td class="td-hide d-none d-sm-table-cell">
                        @if($record->time_out)
                            <span class="badge bg-danger">{{ $record->time_out->format('h:i A') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    {{-- Duration --}}
                    <td class="td-hide d-none d-lg-table-cell">
                        <span class="text-muted">{{ $record->durationLabel() }}</span>
                    </td>
                    {{-- Notifications --}}
                    <td class="td-hide d-none d-lg-table-cell">
                        <div class="d-flex gap-1">
                            <i class="bi bi-{{ $record->notification_sent ? 'envelope-check-fill text-success' : 'envelope text-secondary' }}"
                               title="Time-in {{ $record->notification_sent ? 'sent' : 'not sent' }}"></i>
                            @if($record->time_out)
                                <i class="bi bi-{{ $record->time_out_notification_sent ? 'envelope-check-fill text-primary' : 'envelope-dash text-warning' }}"
                                   title="Time-out {{ $record->time_out_notification_sent ? 'sent' : 'pending' }}"></i>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No attendance records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /table-responsive --}}
    </div>
</div>
{{ $records->links() }}
@endsection
