<?php

use App\Http\Controllers\Admin\AttendanceArchiveController;
use App\Http\Controllers\Admin\CameraController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaceRegistrationController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\AttendanceHistoryController;
use App\Http\Controllers\Teacher\ClassSessionController;
use App\Http\Controllers\Teacher\ManualAttendanceController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\QrAttendanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// Temporary: clear caches from the browser (run once, then remove)
Route::get('temp/clear-caches', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        $output = "OK: caches cleared";
    } catch (\Exception $e) {
        $output = 'ERROR: ' . $e->getMessage();
    }
    return response("<pre>" . htmlspecialchars($output) . "</pre>", 200)->header('Content-Type', 'text/html');
});

// Temporary debug: show last lines of laravel.log
Route::get('debug/logs', function () {
    $file = storage_path('logs/laravel.log');
    if (!file_exists($file)) return response('laravel.log not found', 404);
    $lines = 80;
    $data = shell_exec("tail -n {$lines} " . escapeshellarg($file) . " 2>&1");
    return response("<pre>" . htmlspecialchars($data) . "</pre>", 200)->header('Content-Type', 'text/html');
});

// Temporary debug: check a public/storage path exists and show size
Route::get('debug/check/{path}', function ($path) {
    $decoded = urldecode($path);
    $file = public_path('storage/' . $decoded);
    if (!file_exists($file)) return response("NOT FOUND: {$file}", 404);
    $size = filesize($file);
    return response("FOUND: {$file} ({$size} bytes)", 200)->header('Content-Type', 'text/plain');
})->where('path', '.*');

// Serve files stored under public/storage via a stable proxy path '/s/{path}'
// This avoids problems when the site is served from a /public subfolder
// or when symlinks are not available on the host.
Route::get('s/{path}', function ($path) {
    $decoded = urldecode($path);
    $file = public_path('storage/' . $decoded);
    if (!is_file($file) || !file_exists($file)) {
        abort(404);
    }
    $mime = mime_content_type($file) ?: 'application/octet-stream';
    return Response::file($file, ['Content-Type' => $mime]);
})->where('path', '.*');

// (debug routes removed)

/*
|--------------------------------------------------------------------------
| Web Routes — Face Recognition Based Attendance System
|--------------------------------------------------------------------------
*/

// ─── ROOT / LANDING PAGE — always show landing, never auto-redirect ───────────
Route::get('/', function () {
    return view('landing');
})->name('landing');

// ─── AUTH ROUTES ──────────────────────────────────────────────────────────────
// No guest middleware on GET /login — always show the form even if authenticated
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Face Registration
    Route::prefix('face-registration')->name('face-registration.')->group(function () {
        Route::get('/', [FaceRegistrationController::class, 'index'])->name('index');
        Route::get('/capture/{type}/{id}', [FaceRegistrationController::class, 'capture'])->name('capture');
        Route::post('/store/{type}/{id}', [FaceRegistrationController::class, 'store'])->name('store');
    });

    // Parent Management
    Route::resource('parents', ParentController::class);

    // Camera Management
    Route::resource('cameras', CameraController::class)->except(['show']);
    Route::post('cameras/{camera}/toggle', [CameraController::class, 'toggle'])->name('cameras.toggle');

    // System Settings
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SystemSettingsController::class, 'update'])->name('settings.update');

    // Attendance Archive
    Route::get('attendance', [AttendanceArchiveController::class, 'index'])->name('attendance.index');
    Route::get('attendance/export', [AttendanceArchiveController::class, 'export'])->name('attendance.export');

    // User Management
    Route::resource('users', UserController::class)->except(['show']);
});

// ─── TEACHER ROUTES ───────────────────────────────────────────────────────────
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher'])->group(function () {

    Route::get('/sessions', [ClassSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/stats', [ClassSessionController::class, 'sessionStats'])->name('sessions.stats');
    Route::post('/sessions/start', [ClassSessionController::class, 'start'])->name('sessions.start');
    Route::get('/sessions/{session}/live', [ClassSessionController::class, 'live'])->name('sessions.live');
    Route::get('/sessions/{session}/camera', [ClassSessionController::class, 'camera'])->name('sessions.camera');
    Route::post('/sessions/{session}/stop', [ClassSessionController::class, 'stop'])->name('sessions.stop');
    Route::get('/sessions/{session}/check-schedule', [ClassSessionController::class, 'checkSchedule'])->name('sessions.check-schedule');

    // Manual attendance within a session
    Route::post('/sessions/{session}/manual-attend', [ManualAttendanceController::class, 'store'])
        ->name('sessions.manual-attend');

    // Teacher's own student list
    Route::get('/students',                        [TeacherStudentController::class, 'index'])->name('students.index');
    Route::post('/students',                       [TeacherStudentController::class, 'store'])->name('students.store');
    Route::post('/students/assign',                [TeacherStudentController::class, 'assign'])->name('students.assign');
    Route::delete('/students/{student}/remove',    [TeacherStudentController::class, 'remove'])->name('students.remove');
});

// ─── STUDENT ROUTES ───────────────────────────────────────────────────────────
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/attendance', [AttendanceHistoryController::class, 'index'])->name('attendance.index');
});

// ─── QR ATTENDANCE (public — camera reads student QR card) ───────────────────
// Student personal QR — scanned by teacher camera
Route::post('/attend/student/{token}', [QrAttendanceController::class, 'markByToken'])->name('qr.student.mark');

// Print page — admin prints QR card for a student
Route::get('/admin/students/{student}/qr-print', [QrAttendanceController::class, 'printCard'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.students.qr-print');
