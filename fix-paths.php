<?php
/**
 * Database Path Fix Script for InfinityFree
 * Updates face_encoding and snapshot_path to use new uploads directory
 */

require_once 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\Teacher;
use App\Models\AttendanceRecord;

$studentsFixed = 0;
$teachersFixed = 0;
$recordsFixed = 0;

// Fix student face_encoding paths
$students = Student::whereNotNull('face_encoding')->get();
foreach ($students as $student) {
    $oldPath = $student->face_encoding;
    if (!str_starts_with($oldPath, 'uploads/')) {
        $newPath = 'uploads/' . $oldPath;
        $student->update(['face_encoding' => $newPath]);
        $studentsFixed++;
        echo "Fixed student {$student->id}: {$oldPath} -> {$newPath}\n";
    }
}

// Fix teacher face_encoding paths
$teachers = Teacher::whereNotNull('face_encoding')->get();
foreach ($teachers as $teacher) {
    $oldPath = $teacher->face_encoding;
    if (!str_starts_with($oldPath, 'uploads/')) {
        $newPath = 'uploads/' . $oldPath;
        $teacher->update(['face_encoding' => $newPath]);
        $teachersFixed++;
        echo "Fixed teacher {$teacher->id}: {$oldPath} -> {$newPath}\n";
    }
}

// Fix attendance record snapshot paths
$records = AttendanceRecord::whereNotNull('snapshot_path')->get();
foreach ($records as $record) {
    $oldPath = $record->snapshot_path;
    if (!str_starts_with($oldPath, 'uploads/')) {
        $newPath = 'uploads/' . $oldPath;
        $record->update(['snapshot_path' => $newPath]);
        $recordsFixed++;
        echo "Fixed record {$record->id}: {$oldPath} -> {$newPath}\n";
    }
}

echo "\nSummary:\n";
echo "Students fixed: {$studentsFixed}\n";
echo "Teachers fixed: {$teachersFixed}\n";
echo "Records fixed: {$recordsFixed}\n";
echo "\nDatabase paths updated for InfinityFree!\n";
?>