<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect('/uz');
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
        
        // Redirect /{locale} to /{locale}/dashboard
        Route::get('/', function ($locale) {
            return redirect()->route('web.dashboard', ['locale' => $locale]);
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
