<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/audits', [\App\Http\Controllers\AuditController::class, 'index']);
Route::get('/audits/projects', [\App\Http\Controllers\AuditController::class, 'projects']);
Route::get('/audits/{auditId}/model', [\App\Http\Controllers\AuditController::class, 'modelData']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('users', UserController::class);
});
