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
use App\Http\Controllers\QrAttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Face Recognition Based Attendance System
|--------------------------------------------------------------------------
*/

// ─── ROOT / LANDING PAGE ──────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.sessions.index'),
            'student' => redirect()->route('student.attendance.index'),
            default   => view('landing'),
        };
    }
    return view('landing');
})->name('landing');

// ─── AUTH ROUTES ──────────────────────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::post('/sessions/start', [ClassSessionController::class, 'start'])->name('sessions.start');
    Route::get('/sessions/{session}/live', [ClassSessionController::class, 'live'])->name('sessions.live');
    Route::get('/sessions/{session}/camera', [ClassSessionController::class, 'camera'])->name('sessions.camera');
    Route::post('/sessions/{session}/stop', [ClassSessionController::class, 'stop'])->name('sessions.stop');

    // Manual attendance within a session
    Route::post('/sessions/{session}/manual-attend', [ManualAttendanceController::class, 'store'])
        ->name('sessions.manual-attend');
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
