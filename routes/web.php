<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Docs\DocsController;

Route::get('/', function () {
    return response()->json(['message' => 'Datani bu yerdan topomisan'], 200);
});

Route::get('/docs', [DocsController::class, 'index']);

Route::get('/docs/{name}', [DocsController::class, 'show']);
