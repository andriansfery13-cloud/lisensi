<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\ActivationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\LoaderController;
use App\Http\Controllers\Admin\BlacklistController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', fn() => redirect('/login'));

// Auth Routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes (authenticated)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Licenses (NO DELETE ROUTE!)
    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/create', [LicenseController::class, 'create'])->name('licenses.create');
    Route::post('/licenses', [LicenseController::class, 'store'])->name('licenses.store');
    Route::get('/licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');
    Route::get('/licenses/{license}/edit', [LicenseController::class, 'edit'])->name('licenses.edit');
    Route::put('/licenses/{license}', [LicenseController::class, 'update'])->name('licenses.update');
    Route::patch('/licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::patch('/licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::patch('/licenses/{license}/activate', [LicenseController::class, 'activate'])->name('licenses.activate');
    Route::patch('/licenses/{license}/transfer', [LicenseController::class, 'transfer'])->name('licenses.transfer');

    // Activations
    Route::get('/activations', [ActivationController::class, 'index'])->name('activations.index');
    Route::patch('/activations/{activation}/deactivate', [ActivationController::class, 'deactivate'])->name('activations.deactivate');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit.export');

    // Loader Generator
    Route::get('/loader', [LoaderController::class, 'index'])->name('loader.index');
    Route::post('/loader/generate', [LoaderController::class, 'generate'])->name('loader.generate');
    Route::post('/loader/preview', [LoaderController::class, 'preview'])->name('loader.preview');

    // Domain Blacklist
    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
    Route::post('/blacklist', [BlacklistController::class, 'store'])->name('blacklist.store');
    Route::delete('/blacklist/{blacklist}', [BlacklistController::class, 'destroy'])->name('blacklist.destroy');
});
