<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginAuditController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/audits', [LoginAuditController::class, 'index'])->name('audits.index');
});