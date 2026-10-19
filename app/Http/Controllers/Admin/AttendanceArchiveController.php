<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Camera;
use App\Models\Student;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;

class AttendanceArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['student.user', 'camera', 'classSession'])
            ->orderBy('arrived_at', 'desc');

        if ($request->filled('date'))       $query->whereDate('arrived_at', $request->date);
        if ($request->filled('student_id')) $query->where('student_id', $request->student_id);
        if ($request->filled('camera_id'))  $query->where('camera_id', $request->camera_id);
        if ($request->filled('scan_type'))  $query->where('scan_type', $request->scan_type);

        $pdfRecords = (clone $query)->get()->map(function (AttendanceRecord $record) {
            return [
                'student_key' => $record->student_id,
                'student_name' => $record->student?->user?->name ?? 'Unknown student',
                'student_number' => $record->student?->student_id ?? '—',
                'date' => $record->arrived_at?->format('Y-m-d') ?? '',
                'date_label' => $record->arrived_at?->format('M d, Y') ?? '—',
                'sort_at' => $record->arrived_at?->timestamp ?? 0,
                'time_in' => $record->arrived_at?->format('h:i A') ?? '—',
                'time_out' => $record->time_out?->format('h:i A'),
                'time_out_sort_at' => $record->time_out?->timestamp ?? 0,
                'duration' => $record->durationLabel(),
                'method' => ucfirst(str_replace('_', ' ', $record->method ?? '')) ?: '—',
            ];
        })->values();

        $records  = $query->paginate(30)->withQueryString();
        $students = Student::with('user')->orderBy('id')->get();
        $cameras  = Camera::orderBy('name')->get();

        return view('admin.attendance.index', compact('records', 'students', 'cameras', 'pdfRecords'));
    }

    public function export(Request $request)
    {
        $query = AttendanceRecord::with(['student.user', 'camera'])
            ->where('scan_result', 'success')
            ->orderBy('arrived_at', 'desc');

        if ($request->filled('date'))      $query->whereDate('arrived_at', $request->date);
        if ($request->filled('scan_type')) $query->where('scan_type', $request->scan_type);

        $records = $query->get();

        // Create PHPWord document
        $phpWord = new PhpWord();
        
        // Add document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('DMCMES Attendance System');
        $properties->setTitle('Attendance Report');
        $properties->setSubject('Attendance Records');
        
        // Add a section
        $section = $phpWord->addSection();
        
        // Add title
        $section->addText(
            'Don Marcelo C. Marty Elementary School',
            ['bold' => true, 'size' => 16, 'color' => '0c2d6b'],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Attendance Report',
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Date: ' . ($request->date ?? today()->format('F d, Y')),
            ['size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 300]
        );
        
        // Add table
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '0c2d6b',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER
        ];
        $phpWord->addTableStyle('AttendanceTable', $tableStyle);
        $table = $section->addTable('AttendanceTable');
        
        // Header row
        $table->addRow(500);
        $headerStyle = ['bold' => true, 'size' => 10, 'color' => 'ffffff'];
        $headerCellStyle = ['bgColor' => '0c3d8a', 'valign' => 'center'];
        $table->addCell(3000, $headerCellStyle)->addText('Student Name', $headerStyle);
        $table->addCell(1500, $headerCellStyle)->addText('Student ID', $headerStyle);
        $table->addCell(2000, $headerCellStyle)->addText('Location', $headerStyle);
        $table->addCell(1200, $headerCellStyle)->addText('Method', $headerStyle);
        $table->addCell(1200, $headerCellStyle)->addText('Scan Type', $headerStyle);
        $table->addCell(2000, $headerCellStyle)->addText('Time In', $headerStyle);
        $table->addCell(2000, $headerCellStyle)->addText('Time Out', $headerStyle);
        $table->addCell(1500, $headerCellStyle)->addText('Duration', $headerStyle);
        
        // Data rows
        $cellStyle = ['valign' => 'center'];
        $textStyle = ['size' => 9];
        foreach ($records as $r) {
            $table->addRow();
            $table->addCell(3000, $cellStyle)->addText($r->student->user->name, $textStyle);
            $table->addCell(1500, $cellStyle)->addText($r->student->student_id, $textStyle);
            $table->addCell(2000, $cellStyle)->addText($r->camera->location, $textStyle);
            $table->addCell(1200, $cellStyle)->addText(ucfirst($r->method), $textStyle);
            $table->addCell(1200, $cellStyle)->addText(ucfirst($r->scan_type ?? 'time_in'), $textStyle);
            $table->addCell(2000, $cellStyle)->addText($r->arrived_at->format('M d, Y h:i A'), $textStyle);
            $table->addCell(2000, $cellStyle)->addText($r->time_out ? $r->time_out->format('M d, Y h:i A') : '-', $textStyle);
            $table->addCell(1500, $cellStyle)->addText($r->durationLabel(), $textStyle);
        }
        
        // Add footer
        $section->addTextBreak(1);
        $section->addText(
            'Total Records: ' . $records->count(),
            ['bold' => true, 'size' => 10]
        );
        $section->addText(
            'Generated: ' . now()->format('F d, Y h:i A'),
            ['size' => 9, 'color' => '666666']
        );
        
        // Save to temporary file
        $filename = 'attendance_' . ($request->date ?? today()->toDateString()) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'attendance_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
