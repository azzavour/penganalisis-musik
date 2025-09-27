<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MusicTrackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Music Tracks API Routes
Route::prefix('music-tracks')->group(function () {
    Route::get('/', [MusicTrackController::class, 'index']);
    Route::post('/upload', [MusicTrackController::class, 'upload']);
    Route::post('/preview', [MusicTrackController::class, 'preview']);
    Route::get('/stats', [MusicTrackController::class, 'stats']);
});

// Enable CORS for frontend
Route::middleware(['api'])->group(function () {
    // Your API routes here
});