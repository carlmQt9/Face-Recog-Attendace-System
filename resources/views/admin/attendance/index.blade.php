@extends('layouts.app')
@section('title', 'Attendance Archive')
@section('page-title', 'Attendance Archive')

@section('content')

{{-- Filter Bar --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2 align-items-end" id="attendanceFilters">
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
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <button type="button" class="btn btn-danger btn-sm" onclick="exportToPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
            </div>
            <span id="attendanceFilterStatus" class="visually-hidden" aria-live="polite"></span>
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


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('attendanceFilters');
    if (!filterForm) return;

    const filterControls = filterForm.querySelectorAll('input[name="date"], select[name="student_id"], select[name="camera_id"], select[name="scan_type"]');
    const status = document.getElementById('attendanceFilterStatus');

    filterControls.forEach(control => {
        control.addEventListener('change', function () {
            if (filterForm.dataset.submitting === 'true') return;
            filterForm.dataset.submitting = 'true';
            if (status) status.textContent = 'Updating attendance records…';
            filterForm.requestSubmit();
        });
    });
});

function exportToPDF() {
    const btn = document.querySelector('[onclick="exportToPDF()"]');
    const originalButtonContent = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-file-earmark-arrow-down me-1"></i>Generating...';
    btn.disabled = true;

    try {
        if (!window.jspdf || !window.jspdf.jsPDF || typeof window.jspdf.jsPDF.API.autoTable !== 'function') {
            throw new Error('PDF export libraries could not be loaded.');
        }

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
        const attendanceExportData = @json($pdfRecords);
        const groupedRecords = new Map();

        attendanceExportData.forEach(record => {
            const key = `${record.student_key}|${record.date}`;
            if (!groupedRecords.has(key)) {
                const studentName = String(record.student_name || '')
                    .replace(/\s*(?:[-–—|]\s*)?Admin Device\s*$/i, '')
                    .trim();
                groupedRecords.set(key, {
                    date: record.date,
                    dateLabel: record.date_label,
                    name: studentName || 'Unknown student',
                    studentNumber: record.student_number || '—',
                    firstInAt: null,
                    firstIn: null,
                    lastOutAt: null,
                    lastOut: null,
                    recordCount: 0,
                    methods: new Set()
                });
            }

            const row = groupedRecords.get(key);
            const timeInAt = Number(record.sort_at || 0);
            const timeOutAt = Number(record.time_out_sort_at || 0);
            if (row.firstInAt === null || timeInAt < row.firstInAt) {
                row.firstInAt = timeInAt;
                row.firstIn = record.time_in || '—';
            }
            if (timeOutAt && (row.lastOutAt === null || timeOutAt > row.lastOutAt)) {
                row.lastOutAt = timeOutAt;
                row.lastOut = record.time_out;
            }
            row.recordCount++;
            if (record.method && record.method !== '—') row.methods.add(record.method);
        });

        const reportRows = Array.from(groupedRecords.values()).sort((a, b) =>
            b.date.localeCompare(a.date) || a.name.localeCompare(b.name)
        );
        const body = reportRows.map(row => [
            row.dateLabel,
            row.name,
            row.studentNumber,
            `IN  ${row.firstIn || '—'}\nOUT  ${row.lastOut || 'Not recorded'}`,
            `${row.recordCount} ${row.recordCount === 1 ? 'record' : 'records'}`,
            Array.from(row.methods).join(', ') || '—'
        ]);
        const headers = ['Date', 'Student', 'Student ID', 'Daily Time In / Out', 'Records', 'Method'];
        const title = 'Don Marcelo C. Marty Elementary School';
        const reportDate = @json(request('date') ? \Carbon\Carbon::parse(request('date'))->format('F d, Y') : 'All dates');
        const pageWidth = pdf.internal.pageSize.getWidth();

        pdf.autoTable({
            head: [headers],
            body: body,
            startY: 105,
            margin: { top: 105, right: 34, bottom: 42, left: 34 },
            theme: 'grid',
            styles: {
                font: 'helvetica',
                fontSize: 9,
                cellPadding: { top: 9, right: 9, bottom: 9, left: 9 },
                textColor: [45, 55, 72],
                lineColor: [222, 228, 236],
                lineWidth: 0.5,
                overflow: 'linebreak',
                valign: 'middle'
            },
            headStyles: {
                fillColor: [26, 92, 42],
                textColor: [255, 255, 255],
                fontStyle: 'bold',
                halign: 'left',
                minCellHeight: 26
            },
            columnStyles: {
                0: { cellWidth: 82 },
                1: { cellWidth: 165, fontStyle: 'bold' },
                2: { cellWidth: 100 },
                3: { cellWidth: 190 },
                4: { cellWidth: 92, halign: 'center' },
                5: { cellWidth: 120 }
            },
            alternateRowStyles: { fillColor: [240, 247, 241] },
            didDrawPage: function (data) {
                pdf.setFillColor(26, 92, 42);
                pdf.rect(0, 0, pageWidth, 7, 'F');
                pdf.setTextColor(15, 51, 24);
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(16);
                pdf.text(title, pageWidth / 2, 39, { align: 'center' });
                pdf.setTextColor(90, 102, 120);
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(9);
                pdf.text(`DAILY ATTENDANCE SUMMARY  |  ${reportDate}  |  ${reportRows.length} student-days  |  ${attendanceExportData.length} records`, pageWidth / 2, 57, { align: 'center' });
                pdf.setDrawColor(220, 226, 234);
                pdf.line(34, 70, pageWidth - 34, 70);
            }
        });

        const pageCount = pdf.internal.getNumberOfPages();
        for (let page = 1; page <= pageCount; page++) {
            pdf.setPage(page);
            const pageHeight = pdf.internal.pageSize.getHeight();
            pdf.setDrawColor(220, 226, 234);
            pdf.line(34, pageHeight - 28, pageWidth - 34, pageHeight - 28);
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);
            pdf.setTextColor(107, 114, 128);
            pdf.text(`Generated ${new Date().toLocaleString()}`, 34, pageHeight - 14);
            pdf.text(`Page ${page} of ${pageCount}`, pageWidth - 34, pageHeight - 14, { align: 'right' });
        }

        pdf.save('DMCMES-attendance-report.pdf');
    } catch (error) {
        alert(error.message || 'The PDF download could not be generated. Please try again.');
    } finally {
        btn.innerHTML = originalButtonContent;
        btn.disabled = false;
    }
}
</script>
@endpush
