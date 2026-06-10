<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Warehouse\MedicineController;
use App\Http\Controllers\Warehouse\MedicineTransactionController;
use App\Http\Controllers\HRM\DepartmentController;
use App\Http\Controllers\HRM\PositionController;
use App\Http\Controllers\HRM\StaffController;
use App\Http\Controllers\HRM\EmployeeController;
use App\Http\Controllers\HRM\EmployeeStaffController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', function (Request $request) {
            return $request->user()->load('roles.permissions');
        });

        Route::middleware('role')->group(function () {
            Route::prefix('users')->group(function () {
                Route::get('', [UserController::class, 'index'])->name('user.index');
                Route::post('', [UserController::class, 'store'])->name('user.store');
                Route::put('{id}', [UserController::class, 'update'])->name('user.update');
                Route::delete('{id}', [UserController::class, 'destroy'])->name('user.destroy');
            });

            Route::post('/users/find-from-ldap', [UserController::class, 'findFromLdap'])
                ->name('users.find-from-ldap');

            Route::apiResource('role', \App\Http\Controllers\RoleController::class);
            Route::get('role/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'permissionsForRole']);
            Route::post('role/{role}/sync-permissions', [\App\Http\Controllers\RoleController::class, 'syncPermissions']);

            Route::apiResource('permission', \App\Http\Controllers\PermissionController::class)->only('index');
            Route::get('permission-list', [\App\Http\Controllers\PermissionController::class, 'list'])->name('permission-list');
            Route::put('user-update-role/{id}', [UserController::class, 'updateRole'])->name('user-update-role');
        });
    });
});

// HRM — read-only
Route::middleware(['auth:api', 'role'])->prefix('hrm')->name('hrm.')->group(function () {
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');

    Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('positions/{id}', [PositionController::class, 'show'])->name('positions.show');

    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('staff/{id}', [StaffController::class, 'show'])->name('staff.show');

    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');

    Route::get('employee-staff', [EmployeeStaffController::class, 'index'])->name('employee-staff.index');
    Route::get('employee-staff/{id}', [EmployeeStaffController::class, 'show'])->name('employee-staff.show');
});

// Warehouse (Sklad)
Route::middleware(['auth:api', 'role'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('medicines',         [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('medicines/{id}',    [MedicineController::class, 'show'])->name('medicines.show');
    Route::post('medicines',        [MedicineController::class, 'store'])->name('medicines.store');
    Route::put('medicines/{id}',    [MedicineController::class, 'update'])->name('medicines.update');
    Route::delete('medicines/{id}', [MedicineController::class, 'destroy'])->name('medicines.destroy');

    Route::get('transactions',         [MedicineTransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{id}',    [MedicineTransactionController::class, 'show'])->name('transactions.show');
    Route::post('transactions',        [MedicineTransactionController::class, 'store'])->name('transactions.store');
    Route::delete('transactions/{id}', [MedicineTransactionController::class, 'destroy'])->name('transactions.destroy');
});