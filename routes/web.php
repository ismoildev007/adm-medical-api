<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    $locale = app()->getLocale() ?: 'uz';
    return redirect()->route('web.dashboard', ['locale' => $locale]);
});

// Fallback login for Authenticate middleware without locale
Route::get('/login', function() {
    return redirect()->route('login', ['locale' => app()->getLocale() ?: 'uz']);
})->name('login-fallback');

// ─── API endpoints for Blade (Session based) ────────────────
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/audits', [\App\Http\Controllers\AuditController::class, 'index']);
    Route::get('/audits/projects', [\App\Http\Controllers\AuditController::class, 'projects']);
    Route::get('/audits/stats', [\App\Http\Controllers\AuditController::class, 'stats']);
    Route::get('/audits/{auditId}/model', [\App\Http\Controllers\AuditController::class, 'modelData']);
});

// ─── Multi-language Group ────────────────────────────────
Route::prefix('{locale}')->where(['locale' => 'uz|ru|en'])->group(function () {

    // ─── Auth ───
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── Authenticated: Audit Dashboard ───
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('audits');
        })->name('web.dashboard');

        Route::get('/charts', function () {
            return view('charts');
        })->name('web.charts');

        // Redirect /{locale} to /{locale}/dashboard
        Route::get('/', function () {
            return redirect()->route('web.dashboard', ['locale' => app()->getLocale()]);
        });
    });

    // ─── SuperAdmin Only (Management) ───
    Route::middleware(['auth', 'role'])->prefix('admin')->name('admin.')->group(function () {

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Permissions
        Route::get('/permissions', [\App\Http\Controllers\Web\PermissionController::class, 'index'])->name('permissions.index');

        // Permission sync (JSON endpoints for the modal)
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissionsForRole'])->name('roles.permissions');
        Route::post('/roles/{role}/sync', [RoleController::class, 'syncPermissions'])->name('roles.sync');
    });

});
