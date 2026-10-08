<?php

use App\Http\Controllers\Report\AttendanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,hr'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/attendance', [AttendanceReportController::class, 'index'])->name('attendance');
    Route::get('/attendance/export', [AttendanceReportController::class, 'export'])->name('attendance.export');
});